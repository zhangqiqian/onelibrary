#!/usr/bin/python
# encoding: utf-8

import sys
import time
import pymongo

reload(sys)
sys.setdefaultencoding('utf-8')

USAGE = "Usage: python analyse_similarity.py"

MONGODB = {
    'server': 'localhost',
    'port': 27017,
}

DB_NAME = "onelibrary"
MEMBER_COLLECTION = "t_member"
BOOK_COLLECTION = "t_book"
USER_BOOK_COLLECTION = "t_user_book"
PAPER_COLLECTION = "t_paper"
USER_PAPER_COLLECTION = "t_user_paper"


def db_client(db_config):
    """
    get db connection
    :param db_config: db config
    :return:
    """
    client = pymongo.MongoClient(db_config["server"], db_config["port"])
    return client


def similarity(user_tags, book_tags):
    """
    相似度 = Kq*q/(Kq*q+Kr*r+Ks*s) (Kq > 0 , Kr>=0,Ka>=0)
    q = AB交集,即存在于AB集合中的关键词数量
    Kq = 权重
    r = 存在于A集，不存在于B集的关键词数量
    Kr = 权重
    s = 存在于B集，不存在于A集的关键词数量
    Ks = 权重
    :param book_tags:
    :param user_tags:
    :return:
    """
    user_tag_list = []
    for user_tag in user_tags:
        user_tag_list.append(user_tag['tag'])
    book_tag_list = []
    for book_tag in book_tags:
        book_tag_list.append(book_tag['tag'])

    cmn_tags = []
    diff_book_tags = []
    for book_tag in book_tag_list:
        if book_tag in user_tag_list:
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


def user_book_similarity(client, new_members):
    """
    计算用户与图书的相似性
    :return:
    """
    book_collection = client[DB_NAME][BOOK_COLLECTION]
    user_book_collection = client[DB_NAME][USER_BOOK_COLLECTION]

    start = 0
    limit = 1000
    count = book_collection.count()
    now = int(time.time())
    while start < count:
        books = book_collection.find().sort("book_id").skip(start).limit(limit)
        for new_member in new_members:
            if now - new_member['mtime'] <= 4200:
                books.rewind()
                for book in books:
                    book_id = int(book['book_id'])
                    sim = similarity(new_member['tags'], book['tags'])
                    if sim > 10:
                        record = {
                            'uid': new_member['uid'],
                            'book_id': book_id,
                            'similarity': sim,
                            'status': 0,
                            'mtime': now,
                        }
                        user_book_collection.find_and_modify({'uid': new_member['uid'], 'book_id': book_id, 'status': 0}, record, True)
                    else:
                        user_book_collection.delete_many({'uid': new_member['uid'], 'book_id': book_id, 'status': 0})
        start += limit


def user_paper_similarity(client, new_members):
    """
    计算用户与论文的相似性
    :return:
    """
    paper_collection = client[DB_NAME][PAPER_COLLECTION]
    user_paper_collection = client[DB_NAME][USER_PAPER_COLLECTION]

    start = 0
    limit = 1000
    now = int(time.time())
    count = paper_collection.count() # {"ctime": {"$gte": now - 86400}}
    while start < count:
        papers = paper_collection.find().sort("paper_id").skip(start).limit(limit)
        for new_member in new_members:
            if now - new_member['mtime'] <= 4200:
                papers.rewind()
                for paper in papers:
                    sim = similarity(new_member['tags'], paper['tags'])
                    if sim > 10:
                        record = {
                            'uid': new_member['uid'],
                            'paper_id': paper['paper_id'],
                            'similarity': sim,
                            'status': 0,
                            'mtime': now
                        }
                        user_paper = user_paper_collection.find_one({'uid': new_member['uid'], 'paper_id': paper['paper_id']})
                        if not user_paper or user_paper['status'] == 0:
                            user_paper_collection.find_and_modify({'uid': new_member['uid'], 'paper_id': paper['paper_id']}, record, True)

        start += limit


def main():
    """
    获取所有数据
    :return:
    """
    client = db_client(MONGODB)
    member_collection = client[DB_NAME][MEMBER_COLLECTION]

    members = member_collection.find({'uid': {'$gt': 1}, 'status': 1})
    new_members = []
    paper_new_members = []
    for member in members:
        if 'tags' in member.keys():
            new_members.append({
                'uid': int(member['uid']),
                'mtime': member['mtime'],
                'tags': member['tags'],
                'nickname': member['nickname'],
            })
            if member['grade'] > 1:
                paper_new_members.append({
                    'uid': int(member['uid']),
                    'mtime': member['mtime'],
                    'tags': member['tags'],
                    'nickname': member['nickname'],
                })
    user_book_similarity(client, new_members)
    user_paper_similarity(client, paper_new_members)


if __name__ == "__main__":
    main()
