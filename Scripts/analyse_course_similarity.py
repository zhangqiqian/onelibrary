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


def main():
    """
    获取所有数据
    :return:
    """
    client = db_client(MONGODB)
    currcula_collection = client[DB_NAME][CURRICULA_COLLECTION]
    book_collection = client[DB_NAME][BOOK_COLLECTION]
    course_book_collection = client[DB_NAME][COURSE_BOOK_COLLECTION]

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
                        'tags': new_tags
                    }

    start = 0
    limit = 1000
    count = book_collection.count()
    i = 1
    j = 1
    while start < count:
        books = book_collection.find().sort("book_id").skip(start).limit(limit)
        for book in books:
            book_id = int(book['book_id'])
            print "*"*80
            print "%s: book %s -> %s" % (i, book_id, book["title"])
            i += 1
            for course in courses.values():
                now = int(time.time())
                course_id = course['course_id']

                sim = similarity(course['tags'], book['tags'])
                if sim > 20:
                    print "---- %s: course: %s -> %s" % (j, course['name'], sim)
                    j += 1
                    record = {
                        'course_id': course_id,
                        'book_id': book_id,
                        'similarity': sim,
                        'status': 0,
                        'mtime': now
                    }
                    course_book_collection.find_and_modify({'course_id': course_id, 'book_id': book_id}, record, True)
                else:
                    course_book_collection.delete_many({'course_id': course_id, 'book_id': book_id})
        start += limit


if __name__ == "__main__":
    main()
