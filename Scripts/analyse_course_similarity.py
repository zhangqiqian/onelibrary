#!/usr/bin/python
# encoding: utf-8

import sys
import time
import pymongo
import jieba.analyse
from md5 import md5

reload(sys)
sys.setdefaultencoding('utf-8')

USAGE = "Usage: python analyse_course_similarity.py"

MONGODB = {
    'server': 'localhost',
    'port': 27017,
}

DB_NAME = "onelibrary"
CURRICULA_COLLECTION = "t_curricula"
BOOK_COLLECTION = "t_book"
COURSE_BOOK_COLLECTION = "t_course_book"
PAPER_COLLECTION = "t_paper"
COURSE_PAPER_COLLECTION = "t_course_paper"


def db_client(db_config):
    """
    get db connection
    :param db_config: db config
    :return:
    """
    client = pymongo.MongoClient(db_config["server"], db_config["port"])
    return client


def analyse_keywords(content, topn=5, withweight=True):
    """
    分析单个实例的关键词
    :return:
    """
    tags = jieba.analyse.extract_tags(content, topn, withweight)
    return tags


def similarity(tags, book_tags):
    """
    相似度 = Kq*q/(Kq*q+Kr*r+Ks*s) (Kq > 0 , Kr>=0,Ka>=0)
    q = AB交集,即存在于AB集合中的关键词数量
    Kq = 权重
    r = 存在于A集，不存在于B集的关键词数量
    Kr = 权重
    s = 存在于B集，不存在于A集的关键词数量
    Ks = 权重
    :param book_tags:
    :param course_tags:
    :return:
    """
    tag_list = []
    for tag in tags:
        tag_list.append(tag['tag'])
    book_tag_list = []
    for book_tag in book_tags:
        book_tag_list.append(book_tag['tag'])

    cmn_tags = []
    diff_book_tags = []
    for book_tag in book_tag_list:
        if book_tag in tag_list:
            cmn_tags.append(book_tag)
        else:
            diff_book_tags.append(book_tag)

    sim = 0.0
    if cmn_tags:
        cmn_tag_weight = 0.0
        total_weight = 0.0
        for book_tag in book_tags:
            if book_tag['tag'] in cmn_tags:
                cmn_tag_weight += book_tag['weight']
            total_weight += book_tag['weight']
        sim = round(cmn_tag_weight / total_weight * 100, 1)
    return sim


def course_book_similarity(client, courses):
    """
    计算课程与图书的相似性
    :return:
    """
    book_collection = client[DB_NAME][BOOK_COLLECTION]
    course_book_collection = client[DB_NAME][COURSE_BOOK_COLLECTION]

    start = 0
    limit = 1000
    count = book_collection.count()
    now = int(time.time())
    while start < count:
        books = book_collection.find().sort("book_id").skip(start).limit(limit)
        for course in courses.values():
            course_id = course['course_id']
            if now - course['mtime'] <= 24 * 3600:
                for book in books:
                    book_id = int(book['book_id'])
                    sim = similarity(course['tags'], book['tags'])
                    if sim > 20:
                        record = {
                            'course_id': course_id,
                            'book_id': book_id,
                            'similarity': sim,
                            'status': 0,
                            'mtime': now
                        }
                        course_book_collection.find_and_modify({'course_id': course_id, 'book_id': book_id, "status": 0}, record, True)
                    else:
                        course_book_collection.delete_many({'course_id': course_id, 'book_id': book_id, "status": 0})
        start += limit


def course_paper_similarity(client, courses):
    """
    计算课程与论文的相似性
    :return:
    """
    paper_collection = client[DB_NAME][PAPER_COLLECTION]
    course_paper_collection = client[DB_NAME][COURSE_PAPER_COLLECTION]

    start = 0
    limit = 1000
    count = paper_collection.count()
    now = int(time.time())
    while start < count:
        papers = paper_collection.find().sort("paper_id").skip(start).limit(limit)
        for course in courses.values():
            course_id = course['course_id']
            for paper in papers:
                paper_id = int(paper['paper_id'])
                if (now - paper['ctime']) <= 24 * 3600 or (now - course['mtime']) <= 24 * 3600:
                    sim = similarity(course['tags'], paper['tags'])
                    if sim > 20:
                        record = {
                            'course_id': course_id,
                            'paper_id': paper_id,
                            'similarity': sim,
                            'status': 0,
                            'mtime': now
                        }
                        course_paper_collection.find_and_modify({'course_id': course_id, 'paper_id': paper_id, "status": 0}, record, True)
                    else:
                        course_paper_collection.delete_many({'course_id': course_id, 'paper_id': paper_id, "status": 0})
        start += limit


def main():
    """
    获取所有数据
    :return:
    """
    client = db_client(MONGODB)
    currcula_collection = client[DB_NAME][CURRICULA_COLLECTION]

    curriculas = currcula_collection.find()
    courses = {}
    for curricula in curriculas:
        if curricula['courses']:
            for course in curricula['courses'].values():
                course_id = course['course_id'] if course['course_id'] else md5(course['name']).hexdigest()[0:8]
                if course_id not in courses.keys():
                    tags = analyse_keywords(course['name'], 5, True)
                    new_tags = []
                    for tag in tags:
                        new_tags.append({
                            'tag': tag[0],
                            'weight': tag[1]
                        })
                    courses[course_id] = {
                        'course_id': course_id,
                        'name': course['name'],
                        'tags': new_tags,
                        'mtime': curricula['mtime']
                    }

    course_book_similarity(client, courses)
    # course_paper_similarity(client, courses)

if __name__ == "__main__":
    main()
