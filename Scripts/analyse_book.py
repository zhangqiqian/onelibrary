#!/usr/bin/python
# encoding: utf-8

import sys
import pymongo
import re
import jieba
import jieba.analyse
from optparse import OptionParser

USAGE = "Usage: python analyse_book.py -k [top k] -w [with weight=1 or 0]"

MONGODB = {
    'server': 'localhost',
    'port': 27017,
}

DB_NAME = "onelibrary"
COLLECTION = "t_book"
LOCATION_COLLECTION = "t_location"


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


def analyse(content, topn=10, withweight=True):
    """
    分析单个实例的关键词
    :return:
    """
    tags = jieba.analyse.extract_tags(content, topn, withweight)
    return tags


def main(topn=10, withweight=True):
    """
    获取所有数据
    :return:
    """
    client = db_client(MONGODB)
    collection = client[DB_NAME][COLLECTION]
    books = collection.find()
    prog = re.compile(r"\d+")
    i = 0
    for book in books:
        i += 1
        print "%s: %s" % (i, book['title'])
        content = "%s %s %s" % (book['title'], book['summary'], ','.join(book['subject']))
        tags = analyse(content, topn, withweight)
        new_tags = []
        for tag in tags:
            result = prog.match(tag[0])
            if not result:
                new_tags.append({"tag": tag[0], "weight": tag[1]})
        book['tags'] = new_tags
        collection.save(book)


def get_next_sequence(name, client):
    """
    需要首先创建一个计数器，然后再获取自增的数字
    db.counters.insert(
        {
            id: "book_id",
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


def insert_book_id():
    client = db_client(MONGODB)
    collection = client[DB_NAME][COLLECTION]
    books = collection.find()
    for book in books:
        book_id = get_next_sequence("book_id", client)
        book["book_id"] = book_id
        print "%s: %s" % (book_id, book["title"])
        collection.save(book)

def command():
    parser = OptionParser(USAGE)
    parser.add_option("-k", dest="topK")
    parser.add_option("-w", dest="withWeight")
    opt, args = parser.parse_args()

    if len(args) < 1:
        print(USAGE)
        sys.exit(1)

    if opt.topK is None:
        topK = 10
    else:
        topK = int(opt.topK)

    if opt.withWeight is None:
        withWeight = True
    else:
        if int(opt.withWeight) is 1:
            withWeight = True
        else:
            withWeight = False

    main(topK, withWeight)

if __name__ == "__main__":
    main()
    # command()
    # insert_book_id()
