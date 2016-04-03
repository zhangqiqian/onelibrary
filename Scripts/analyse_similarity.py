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


def main():
    """
    获取所有数据
    :return:
    """
    client = db_client(MONGODB)
    member_collection = client[DB_NAME][MEMBER_COLLECTION]
    book_collection = client[DB_NAME][BOOK_COLLECTION]
    user_book_collection = client[DB_NAME][USER_BOOK_COLLECTION]

    books = book_collection.find()
    members = member_collection.find()
    for book in books:
        members.rewind()
        book_id = int(book['book_id'])
        for member in members:
            now = int(time.time())
            if now - member['mtime'] <= 3600:
                uid = int(member['uid'])
                if uid == 1:
                    continue

                # get old user book record and compare similarity
                user_book = user_book_collection.find_one({'uid': uid, 'book_id': book_id})
                sim = similarity(member['tags'], book['tags'])
                if sim > 0:
                    if user_book and sim <= user_book['similarity']:
                        continue
                    record = {
                        'uid': uid,
                        'book_id': book_id,
                        'similarity': sim,
                        'status': 0,
                        'mtime': now,
                        }
                    user_book_collection.find_and_modify({'uid': uid, 'book_id': book_id}, record, True)
                else:
                    user_book_collection.delete_many({'uid': uid, 'book_id': book_id})

if __name__ == "__main__":
    main()