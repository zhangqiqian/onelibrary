#!/usr/bin/python
# encoding: utf-8

import sys
import pymongo
import time
import jieba
import jieba.analyse
from optparse import OptionParser

reload(sys)
sys.setdefaultencoding('utf-8')

USAGE = "Usage: python analyse_user.py"

MONGODB = {
    'server': 'localhost',
    'port': 27017,
}

DB_NAME = "onelibrary"
MEMBER_COLLECTION = "t_member"
CURRICULA_COLLECTION = "t_curricula"

MAJOR_LIST = {
    1: "图书馆学",
    2: "情报学",
    3: "档案学",
    4: "信息管理与信息系统",
    5: "计算机",
    6: "图书情报专业硕士"
}


def db_client(db_config):
    """
    get db connection
    :param db_config: db config
    :return:
    """
    client = pymongo.MongoClient(db_config["server"], db_config["port"])
    return client


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
    member_collection = client[DB_NAME][MEMBER_COLLECTION]
    members = member_collection.find({'status': 1})
    for member in members:
        if member['uid'] == 1:
            continue
        major = MAJOR_LIST[member['major']] if member['major'] > 0 else ''
        interests = member['interests'] if 'interests' in member.keys() else []
        research = member['research'] if 'research' in member.keys() else []
        # courses = []
        # if 'curricula_id' in member.keys() and member['curricula_id'] > 0:
        #     curricula_collection = client[DB_NAME][CURRICULA_COLLECTION]
        #     curricula = curricula_collection.find_one({"curricula_id": member['curricula_id']})
        #     for course in curricula['courses'].values():
        #         courses.append(course['name'].strip())
        words = [major] + interests + research
        content = ','.join(words)
        all_tags = analyse(content, topn, withweight)

        member_tags = []
        for tag in all_tags:
            member_tags.append({'tag': tag[0], 'weight': tag[1]})
        member['tags'] = member_tags
        member['mtime'] = int(time.time())
        member_collection.save(member)


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