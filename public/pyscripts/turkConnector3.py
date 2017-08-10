#!/usr/bin/env python
import boto3
import time
import datetime
import oceler_args

def log(s):
    f = open('/Applications/MAMP/htdocs/oceler/public/pyscripts/turk-connector.log','a')
    f.write(datetime.datetime.now().ctime() + " :: " + s + "\n")
    f.close()

def approve_assignment(mturk, args):
    response = mturk.approve_assignment(AssignmentId = args.assignment)
    log("Approved assignment " + args.assignment)
    log(str(response))
    if not response:
        return 1
    else:
        return 0

def reject_assignment(mturk, args):
    response = mturk.reject_assignment(AssignmentId = args.assignment)
    log("Rejected assignment " + args.assignment)
    log(str(response))
    if not response:
        return 1
    else:
        return 0

def process_bonus(mturk, args):
    if float(args.bonus) > 15.00:
        args.bonus = '15.00'
    response = mturk.send_bonus(WorkerId = args.worker,
                          AssignmentId = args.assignment,
                          #bonus_price = (boto.mturk.price.Price( amount = args.bonus)),
                          BonusAmount = args.bonus,
                          Reason = "Additional compensation",
                          UniqueRequestToken = args.unique_token)
    log("Paid " + args.bonus + " bonus to " + args.worker + " for assignment " + args.assignment)
    log(str(response))
    if not response:
        return 1
    else:
        return 0

def process_qualification(mturk, args):
    response = mturk.associate_qualification_with_worker(QualificationTypeId = args.qual_id,
                               WorkerId = args.worker,
                               IntegerValue = args.qual_val,
                               SendNotification = True)

    log("Updated qualification for " + args.worker + " to " + args.qual_val)
    log(str(response))

    if not response:
        return 1
    else:
        return 0

def test_connection(mturk, args):
    response = mturk.get_account_balance()
    log("testing connection.")
    log(str(response))
    return 1

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
    print(approve_assignment(mturk, args))

if args.func == 'reject_assignment':
    print(reject_assignment(mturk, args))

if args.func == 'process_bonus':
    print(process_bonus(mturk, args))

if args.func == 'process_qualification':
    print(process_qualification(mturk, args))

if args.func == 'test_connection':
    print(test_connection(mturk, args))
