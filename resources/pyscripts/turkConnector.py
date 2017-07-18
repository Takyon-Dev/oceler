#!/usr/bin/env python
import boto.mturk.connection
import boto.mturk.question
import time
import datetime
import oceler_args

import os

sandbox_host = 'mechanicalturk.sandbox.amazonaws.com'
real_host = 'mechanicalturk.amazonaws.com'

args = oceler_args.parser.parse_args()

print(args.reason)

if args.delay:
    time.sleep(float(args.delay))

if args.host == 'sandbox':
    host = sandbox_host
else:
    host = real_host

#f = open("../storage/logs/turk-connector.log","a")
f = open("turk-connector.log","a") # for cmd line testing
f.write('Connecting to ' + host + ' ' +  datetime.datetime.now().ctime() + "\r\n")
f.write('Logging assignment: ' + args.assignment + "\r\n")

mturk = boto.mturk.connection.MTurkConnection(
    aws_access_key_id = args.acc_key,
    aws_secret_access_key = args.sec_key,
    host = host,
    debug = 1 # debug = 2 prints out all requests. but we'll just keep it at 1
)

if args.trial_completed == 'true':
    #mturk.approve_assignment(assignment_id = args.assignment)
    f.write('Worker: ' + args.worker + ' -- Approving assignment ' + args.assignment + "\r\n")
else:
        #mturk.reject_assignment(assignment_id = args.assignment)
        f.write('Worker: ' + args.worker + ' -- Rejecting assignment ' + args.assignment + "\r\n")

if float(args.bonus) > 0:
        mturk.grant_bonus(worker_id = args.worker,
                          assignment_id = args.assignment,
                          bonus_price = (boto.mturk.price.Price( amount = args.bonus)),
                          reason = "")
        f.write('Worker: ' + args.worker + ' -- paying bonus ' + args.bonus + "\r\n")

if args.trial_passed == 'true':
    mturk.assign_qualification(qualification_type_id = args.qual_id,
                               worker_id = args.worker,
                               value = args.qual_val,
                               send_notification = True)
    f.write('Worker: ' + args.worker + ' -- updating qualification ' + args.qual_id + " to " + args.qual_val + "\r\n")

f.write("END\r\n")
f.close()
exit()
