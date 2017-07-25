#!/usr/bin/env python
import boto.mturk.connection
import boto.mturk.question
import time
import datetime
import oceler_args

import os

def process_assignment(mturk, args):
    if args.trial_completed == '1':
        response = mturk.approve_assignment(assignment_id = args.assignment)
    else:
        response = mturk.reject_assignment(assignment_id = args.assignment)

    if not response:
        return 0
    else:
        return 1

def process_bonus(mturk, args):
    if float(args.bonus) > 0:
        response = mturk.grant_bonus(worker_id = args.worker,
                              assignment_id = args.assignment,
                              bonus_price = (boto.mturk.price.Price( amount = args.bonus)),
                              reason = "Additional compensation")

        if not response:
            return 0
        else:
            return 1

def process_qualification(mturk, args):
    if args.trial_passed == '1':
        if args.qual_val == 1:
            response = mturk.assign_qualification(qualification_type_id = args.qual_id,
                                       worker_id = args.worker,
                                       value = args.qual_val,
                                       send_notification = True)
        else:
            response = mturk.update_qualification_score(qualification_type_id = args.qual_id,
                                       worker_id = args.worker,
                                       value = args.qual_val)

        if not response:
            return 0
        else:
            return 1

sandbox_host = 'mechanicalturk.sandbox.amazonaws.com'
real_host = 'mechanicalturk.amazonaws.com'

args = oceler_args.parser.parse_args()

if args.delay:
    time.sleep(float(args.delay))

if args.host == 'sandbox':
    host = sandbox_host
else:
    host = real_host

mturk = boto.mturk.connection.MTurkConnection(
    aws_access_key_id = args.acc_key,
    aws_secret_access_key = args.sec_key,
    host = host,
    debug = 1 # debug = 2 prints out all requests. but we'll just keep it at 1
)


if args.func == 'process_assignment':
    result = process_assignment(mturk, args)

if args.func == 'process_bonus':
    result = process_bonus(mturk, args)

if args.func == 'process_qualification':
    result = process_qualification(mturk, args)
