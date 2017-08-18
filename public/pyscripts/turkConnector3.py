#!/usr/bin/env python
import boto3
from botocore.exceptions import ClientError, BotoCoreError
import time
import datetime
import oceler_args

def log(s):
    f = open(args.log_path,'a')
    f.write(datetime.datetime.now().ctime() + " :: " + s + "\n")
    f.close()

def approve_assignment(mturk, args):
    log("Approving assignment " + args.assignment)

    try:
        mturk.approve_assignment(AssignmentId = args.assignment)
        return 0

    except ClientError as e:
        log(e.response['Error']['Message'])
        return 1

    except BotoCoreError as ex:
        log(str(ex.fmt))
        return 1

def reject_assignment(mturk, args):
    log("Rejecting assignment " + args.assignment)

    try:
        mturk.reject_assignment(AssignmentId = args.assignment,
                                RequesterFeedback = "We were not able to run a complete trial. We will send the consololation fee as a bonus payment.")
        return 0

    except ClientError as e:
        log(e.response['Error']['Message'])
        return 1

    except BotoCoreError as ex:
        log(str(ex.fmt))
        return 1

def process_bonus(mturk, args):
    if float(args.bonus) > 15.00:
        args.bonus = '15.00'

    log("Paying " + args.bonus + " bonus to " + args.worker + " for assignment " + args.assignment)

    try:
        mturk.send_bonus(WorkerId = args.worker,
                         AssignmentId = args.assignment,
                         BonusAmount = args.bonus,
                         Reason = "Additional compensation",
                         UniqueRequestToken = args.unique_token)

        return 0

    except ClientError as e:
        log(e.response['Error']['Message'])
        return 1

    except BotoCoreError as ex:
        log(str(ex.fmt))
        return 1

def process_qualification(mturk, args):

    log("Updating qualification for " + args.worker + " to " + args.qual_val)

    try:
        mturk.associate_qualification_with_worker(QualificationTypeId = args.qual_id,
                                   WorkerId = args.worker,
                                   IntegerValue = int(args.qual_val),
                                   SendNotification = True)

        return 0

    except ClientError as e:
        log(e.response['Error']['Message'])
        return 1

    except BotoCoreError as ex:
        log(str(ex.fmt))
        return 1

def test_connection(mturk, args):
    response = mturk.get_account_balance()
    log("testing connection.")
    log(str(response))
    return 0

sandbox_endpoint = 'https://mturk-requester-sandbox.us-east-1.amazonaws.com'
real_endpoint = 'https://mturk-requester.us-east-1.amazonaws.com'

args = oceler_args.parser.parse_args()

if args.host == 'real':
    endpoint = real_endpoint
else:
    endpoint = sandbox_endpoint

mturk = boto3.client('mturk',
                     aws_access_key_id = args.acc_key,
                     aws_secret_access_key = args.sec_key,
                     endpoint_url = endpoint,
                     region_name='us-east-1'
)


if args.func == 'approve_assignment':
    exit(approve_assignment(mturk, args))

if args.func == 'reject_assignment':
    exit(reject_assignment(mturk, args))

if args.func == 'process_bonus':
    exit(process_bonus(mturk, args))

if args.func == 'process_qualification':
    exit(process_qualification(mturk, args))

if args.func == 'test_connection':
    exit(test_connection(mturk, args))
