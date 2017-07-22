#!/usr/bin/env python
import boto.mturk.connection
import boto.mturk.question
import time
import oceler_args

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

# Change title, description, keywords, max assignments and amount as needed
URL = "https://netlabexperiments.org/MTurk-login"
title = "OCELER TEST 05"
description = "Testing the OCELER app as an external question"
keywords = ["oceler", "test", "external"]
frame_height = 600 # the height of the iframe holding the external hit
amount = .01 # base payment
max_assignments = 5 # number of subjects

questionform = boto.mturk.question.ExternalQuestion( URL, frame_height )

create_hit_result = mturk.create_hit(
    title = title,
    description = description,
    keywords = keywords,
    question = questionform,
    reward = boto.mturk.price.Price( amount = amount ),
    max_assignments = max_assignments,
    response_groups = ( 'Minimal', 'HITDetail' ), # I don't know what response groups are yet
    )

HIT = create_hit_result[0]
assert create_hit_result.status

print '[create_hit( %s, $%s ): %s]' % ( URL, amount, HIT.HITId )

exit()

# Change title, description, keywords, max assignments and amount as needed
URL = "https://netlabexperiments.org/MTurk-login"
title = "OCELER TEST 05"
description = "Testing the OCELER app as an external question"
keywords = ["oceler", "test", "external"]
frame_height = 600 # the height of the iframe holding the external hit
amount = .01 # base payment
max_assignments = 5 # number of subjects

questionform = boto.mturk.question.ExternalQuestion( URL, frame_height )

create_hit_result = mturk.create_hit(
    title = title,
    description = description,
    keywords = keywords,
    question = questionform,
    reward = boto.mturk.price.Price( amount = amount ),
    max_assignments = max_assignments,
    response_groups = ( 'Minimal', 'HITDetail' ), # I don't know what response groups are yet
    )

HIT = create_hit_result[0]
assert create_hit_result.status

print '[create_hit( %s, $%s ): %s]' % ( URL, amount, HIT.HITId )


exit()

# 3WPCIUYH19791IRR0WVRO93DRKEDTL
exit()

sandbox_host = 'mechanicalturk.sandbox.amazonaws.com'
real_host = 'mechanicalturk.amazonaws.com'

mturk = boto.mturk.connection.MTurkConnection(
    aws_access_key_id = 'AKIAJTMGGTPGKJJS2SGA',
    aws_secret_access_key = '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',
    host = sandbox_host,
    debug = 1 # debug = 2 prints out all requests. but we'll just keep it at 1
)

print boto.Version
print mturk.get_account_balance()


# Change title, description, keywords, max assignments and amount as needed
URL = "https://netlabexperiments.org/MTurk-login"
title = "OCELER TEST 03"
description = "Testing the OCELER app as an external question"
keywords = ["oceler", "test", "external"]
frame_height = 600 # the height of the iframe holding the external hit
amount = .01 # base payment
max_assignments = 5 # number of subjects

questionform = boto.mturk.question.ExternalQuestion( URL, frame_height )

create_hit_result = mturk.create_hit(
    title = title,
    description = description,
    keywords = keywords,
    question = questionform,
    reward = boto.mturk.price.Price( amount = amount ),
    max_assignments = max_assignments,
    response_groups = ( 'Minimal', 'HITDetail' ), # I don't know what response groups are yet
    )

HIT = create_hit_result[0]
assert create_hit_result.status

print '[create_hit( %s, $%s ): %s]' % ( URL, amount, HIT.HITId )
