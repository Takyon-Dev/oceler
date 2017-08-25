import boto3

client = boto3.client('mturk',
                        aws_access_key_id = 'AKIAJTMGGTPGKJJS2SGA',
                        aws_secret_access_key = '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',
                        endpoint_url = 'https://mturk-requester-sandbox.us-east-1.amazonaws.com',
                        region_name = 'us-east-1')

external_content = '<ExternalQuestion xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd"><ExternalURL>https://netlabexperiments.org/MTurk-login</ExternalURL><FrameHeight>600</FrameHeight></ExternalQuestion>'


response = client.create_hit(
    MaxAssignments=9,
    LifetimeInSeconds=50000, # REQUIRED - Time, in seconds, after which the HIT is no longer available for users to accept.
    AssignmentDurationInSeconds=500, # REQUIRED - Seconds that a Worker has to complete the HIT after accepting it.
    Reward='0.40', # REQUIRED
    Title='OCELER TEST',
    Keywords='oceler',
    Description='Testing OCELER platform',
    Question=external_content,
    QualificationRequirements=[
        {
            'QualificationTypeId': '3DDNYIPUQNTSBR52F1XBRX6XW33RZA', # The ID of your qualification
            'Comparator': 'EqualTo',
            'IntegerValues': [
                3,
            ]
        },
    ]
)
