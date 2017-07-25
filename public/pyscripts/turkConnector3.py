#!/usr/bin/env python
import boto3
import time
import datetime
import oceler_args

def log(s):
    f = open('turk-connector.log','a')
    f.write(datetime.datetime.now().ctime() + " :: " + s + "\n")
    f.close()

def process_assignment(mturk, args):
    if args.trial_completed == '1':
        response = mturk.approve_assignment(AssignmentId = args.assignment)
        log("Approved assignment " + args.assignment)
    else:
        response = mturk.reject_assignment(AssignmentId = args.assignment)
        log("Rejected assignment " + args.assignment)

    if not response:
        return 0
    else:
        return 1

def process_bonus(mturk, args):
    if float(args.bonus) > 0:
        if float(args.bonus) > 15.00:
            args.bonus = '15.00'
        response = mturk.send_bonus(WorkerId = args.worker,
                              AssignmentId = args.assignment,
                              #bonus_price = (boto.mturk.price.Price( amount = args.bonus)),
                              BonusAmount = args.bonus
                              Reason = "Additional compensation",
                              UniqueRequestToken = args.unique_token)
        log("Paid " + args.bonus + " bonus to " + args.worker " for assignment " + args.assignment)

    if not response:
        return 0
    else:
        return 1

def process_qualification(mturk, args):

    response = mturk.associate_qualification_with_worker(QualificationTypeId = args.qual_id,
                               WorkerId = args.worker,
                               IntegerValue = args.qual_val,
                               SendNotification = True)


    if not response:
        log("Updated qualification for " + args.worker " to " + args.qual_val)
        return 0
    else:
        return 1

sandbox_endpoint = 'https://mturk-requester-sandbox.us-east-1.amazonaws.com'
real_endpoint = 'https://mturk-requester.us-east-1.amazonaws.com

args = oceler_args.parser.parse_args()

if args.host == 'sandbox':
    endpoint = sandbox_endpoint
else:
    endpoint = real_endpoint

mturk = boto3.client('mturk',
                     aws_access_key_id = args.acc_key,
                     aws_secret_access_key = args.sec_key,
                     endpoint_url = endpoint,
                     region_name='us-west-2'
)


if args.func == 'process_assignment':
    result = process_assignment(mturk, args)

if args.func == 'process_bonus':
    result = process_bonus(mturk, args)

if args.func == 'process_qualification':
    result = process_qualification(mturk, args)
