#!/usr/bin/python
# -*- coding: utf-8 -*-

import sys
import re
import pymongo
import time
import cookielib
from bs4 import BeautifulSoup
from optparse import OptionParser
import urllib2
import urllib


USAGE = "Usage: python cnki_spider.py"

MONGODB = {
    'server': 'localhost',
    'port': 27017,
}

DB_NAME = "onelibrary"
COLLECTION = "t_paper"
KEYWORDS_COLLECTION = "t_preference"

PAPER_SEARCH_TYPES = [
    'SU',   # 主题
    'TI',   # 标题
    'KY',   # 关键词
]

# 构建头结构
CNKI_COMMON_HEADERS = {
    'Referer': 'http://epub.cnki.net/kns/brief/result.aspx?dbprefix=scdb&action=scdbsearch&db_opt=SCDB',
    'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
    'Connection':'keep-alive',
    'Host':'epub.cnki.net',
}


def db_client(db_config):
    """
    get db connection
    :param db_config: db config
    :return:
    """
    client = pymongo.MongoClient(db_config["server"], db_config["port"])
    return client


def get_collection(client, db_name, collection):
    """
    get collection
    :param client: client
    :param db_name: db name
    :param collection: collection name
    :return: connection
    """
    collection = client[db_name][collection]
    return collection


def save(client, db_name, collection, items):
    """
    save data to local db
    :param client: client
    :param db_name: db name
    :param collection: collection name
    :param items: data list
    :return: None
    """
    if items:
        local_table = get_collection(client, db_name, collection)
        for item in items:
            ret = local_table.save(item)


def get_next_sequence(name, client):
    """
    需要首先创建一个计数器，然后再获取自增的数字
    db.counters.insert(
        {
            id: "paper_id",
            seq: 0
        }
    )
    :param name:
    :param client:
    :return:
    """
    counters = client[DB_NAME]['counters']
    ret = counters.find_and_modify({"id": name}, {"$inc": {"seq": 1}}, True)
    return ret['seq']


def fetch_papers(keyword='', start_time=0, end_time=0, topn=50):
    """
    抓取期刊内容
    知网需要两次发送请求,一次为参数请求,一次为返回页面请求
    :param keyword:
    :param start_time:
    :param end_time:
    :param topn:
    :return:
    """
    # 生成cookie
    cookies = cookielib.CookieJar()
    cookie_support = urllib2.HTTPCookieProcessor(cookies)
    # 创建一个新的opener来使用cookiejar
    opener = urllib2.build_opener(cookie_support, urllib2.HTTPHandler)
    urllib2.install_opener(opener)

    # 查询时间范围
    if end_time == 0:
        end_time = time.time()

    if start_time == 0:
        start_time = end_time - 30 * 24 * 3600

    start_time_string = time.strftime('%Y-%m-%d', time.gmtime(start_time))
    end_time_string = time.strftime('%Y-%m-%d', time.gmtime(end_time))

    for search_type in PAPER_SEARCH_TYPES:
        # 构建第一次提交参数
        now_format = time.strftime('%a %b %d %Y %H:%M:%S GMT%z (%Z)')
        first_params = {
            'action': '',
            'NaviCode': '*',
            'ua': '1.21',
            'PageName': 'ASP.brief_result_aspx',
            'DbPrefix': 'SCDB',
            'DbCatalog': '中国学术文献网络出版总库',
            'ConfigFile': 'SCDB.xml',
            'db_opt': 'CJFQ,CJFN,CDFD,CMFD,CPFD,IPFD,CCND,CCJD,HBRD',
            'base_special1': '%',
            'magazine_special1': '%',
            'publishdate_from': start_time_string,
            'publishdate_to': end_time_string,
            'txt_1_sel': search_type,
            'txt_1_value1': keyword,
            'txt_1_relation': '#CNKI_AND',
            'txt_1_special1': '=',
            'his': '0',
            '__': now_format,
        }

        first_query_string = urllib.urlencode(first_params)
        basr_url = 'http://epub.cnki.net/KNS/request/SearchHandler.ashx?' + first_query_string
        urllib2.urlopen(urllib2.Request(basr_url, headers=CNKI_COMMON_HEADERS))

        # 构建第二次提交参数，不过貌似这些参数对返回值没有影响，尝试了修改keyValue和spvalue依然能正常返回
        second_params = {
            'pagename': 'ASP.brief_result_aspx',
            'DbCatalog': '中国学术文献网络出版总库',
            'ConfigFile': 'SCDB.xml',
            'research': 'off',
            't': int(time.time() * 1000),
            'keyValue': keyword,
            'dbPrefix': 'SCDB',
            'S': '1',
            'RecordsPerPage': topn,
        }
        second_query_string = urllib.urlencode(second_params)
        search_url = 'http://epub.cnki.net/kns/brief/brief.aspx?' + second_query_string
        response = urllib2.urlopen(urllib2.Request(search_url, headers=CNKI_COMMON_HEADERS))
        content = response.read()

        # 搜索首页, 获取总页数
        mth = re.search(r'浏览1/(\d+)', content)
        pages = 1
        if mth:
            pages_str = mth.groups()[0]
            pages_str = pages_str.replace(',', '')
            pages = int(pages_str)

        # 处理第一页
        papers = handle_papers(content)
        # 保存第一页的内容到数据库
        save_papers(papers)

        # 搜索下一页
        for i in range(pages):
            if i == 0:  # 排除第一页
                continue

            next_params = {
                'curpage': i+1,
                'RecordsPerPage': topn,
                'QueryID': 0,
                'ID': '',
                'turnpage': 1,
                'tpagemode': 'L',
                'dbPrefix': 'SCDB',
                'Fields': '',
                'DisplayMode': 'listmode',
                'PageName': 'ASP.brief_result_aspx'
            }
            next_query_string = urllib.urlencode(next_params)
            next_url = 'http://epub.cnki.net/kns/brief/brief.aspx?' + next_query_string
            next_response = urllib2.urlopen(urllib2.Request(next_url, headers=CNKI_COMMON_HEADERS))
            next_content = next_response.read()

            # 处理下一页
            next_papers = handle_papers(next_content)
            # 保存下一页的内容到数据库
            save_papers(next_papers)


def handle_papers(papers_content=''):
    """
    处理查询得到的论文列表及论文详细
    :param papers_content:
    :return:
    """
    base_uri = 'http://www.cnki.net'

    papers = []
    if not papers_content:
        return papers

    soup = BeautifulSoup(papers_content)
    table_tr_list = soup.find('table', {'class': 'GridTableContent'}).find_all('tr', {'bgcolor': True})
    for table_tr in table_tr_list:
        # 获取论文的部分字段
        table_td_list = table_tr.find_all('td')
        link = table_td_list[1].find('a', {'href': True})
        href = link.get("href")
        new_href = href.replace('kns', 'KCMS', 1)
        detail_url = "%s%s" % (base_uri, new_href)
        author = table_td_list[2].get_text().strip()
        author = author.replace(' ', '')
        publisher = table_td_list[3].get_text().strip()
        pubdate = table_td_list[4].get_text().strip()
        paper_type = table_td_list[5].get_text().strip()
        quote_count = table_td_list[6].get_text().strip()
        download_count = table_td_list[7].get_text().strip()

        # 从内容中获取部分字段
        detail_resp = urllib2.urlopen(urllib2.Request(detail_url, headers=CNKI_COMMON_HEADERS))
        detail_content = detail_resp.read()
        data = detail_paper(detail_content)

        # 合并字段
        data['link'] = detail_url
        data['pubdate'] = pubdate
        data['paper_type'] = paper_type
        data['quote_count'] = quote_count
        data['download_count'] = download_count
        if not data['author']:
            data['author'] = author
        if not data['journal']:
            data['journal'] = publisher
        if not data['period']:
            data['period'] = pubdate

        papers.append(data)
    return papers


def detail_paper(detail_content=''):
    """
    获取详细内容
    :param detail_content:
    :return:
    """
    if not detail_content:
        return False

    detail_content = detail_content.replace('utf-16', 'utf-8')
    soup = BeautifulSoup(detail_content)

    # Title
    title_span = soup.find('span', {'id': 'chTitle'})
    title = title_span.get_text().strip()
    title = title.replace('\n', '')
    title = title.replace(' ', '')

    # Journal
    journal_soup = soup.find('div', {'class': 'detailLink'})
    journal = []
    if journal_soup:
        journal_a_list = journal_soup.find_all('a', {'onclick': True})
        for journal_a in journal_a_list:
            journal.append(journal_a.get_text().strip())

    # Author
    author_soup = soup.find('div', {'class': 'author'})
    authors = []
    institutions = []
    if author_soup:
        author_a_list = author_soup.find_all('a', {'href': True})
        for author_a in author_a_list:
            href = author_a.get('href')
            if href and 'sfield=au' in href:
                authors.append(author_a.get_text().strip())
            if href and 'sfield=inst' in href:
                institutions.append(author_a.get_text().strip())

    # Summary
    summary_span = soup.find('span', {'id': 'ChDivSummary'})
    summary = summary_span.get_text().strip()
    summary = summary.replace(' ', '')

    # Keywords
    keywords_a_list = soup.find('span', {'id': 'ChDivKeyWord'}).find_all('a')
    keywords = []
    for keywords_a in keywords_a_list:
        keywords.append(keywords_a.get_text().strip())

    # Project
    project_li_list = soup.find_all('div', {'class': 'keywords int5'})
    project = ''
    for project_li in project_li_list:
        text = project_li.get_text().strip()
        if u'【基金】' in text:
            text = text.replace(u'【基金】', '')
            project = text.strip()
            break

    data = {
        'title': title,
        'author': ';'.join(authors),
        'institution': ';'.join(institutions),
        'summary': summary,
        'journal': journal[0] if len(journal) > 0 else '',
        'period': journal[2] if len(journal) > 2 else '',
        'keywords': keywords,
        'project': project,
        'status': 0,
        'ctime': int(time.time()),
        'source': 'cnki',
    }
    return data


def save_papers(papers):
    """
    获取网页Top数据
    :param papers:
    :return:
    """
    client = db_client(MONGODB)
    collection = client[DB_NAME][COLLECTION]
    for paper in papers:
        old_paper = collection.find({'title': paper["title"], 'author': paper["author"]})
        if old_paper:
            continue
        paper_id = get_next_sequence("paper_id", client)
        paper["paper_id"] = paper_id
        print "%s: %s" % (paper_id, paper["title"])
        collection.save(paper)


def main(keyword='', topK=50):
    """

    :return:
    """
    pass


def command():
    parser = OptionParser(USAGE)
    parser.add_option("-w", dest="keyword")
    parser.add_option("-k", dest="topK")
    opt, args = parser.parse_args()

    if len(args) < 1:
        print(USAGE)
        sys.exit(1)

    if opt.topK is None:
        topK = 50
    else:
        topK = int(opt.topK)

    main(opt.keyword, topK)

if __name__ == "__main__":
    # command()
    # fetch_papers('图书馆')
    # print get_cookie()
    # detail_paper()
    main()