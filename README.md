THE TEST

Introduction

We want to create a portal where users can log in, reset password, sign up and update profile.
What we’ll need from you, is to create the API’s that will enable these features.
While building this out, we’ll also need you to think about how you’ll pipe these requests to our
Analytics, Messaging & Monitoring platforms i.e. Google Analytics, New Relic, DataDog, ...

Requirements

A user account will include a name, email, password, phone number and billing address.
Help us create 4 endpoints -
1. Signup
a. Include name, email and password and returns signed JWT token
b. Validations
i. Validate if the email has been registered before
c. Send to Messaging platform with the payload after signup:
i. User attributes: email, current timestamp, user id
ii. Event name: Sign Up
d. Send a welcome mailer.
2. Login
a. Accept email and password, and return a JWT token
b. Send to Segment with the payload after login:
i. User attributes: Email, current timestamp, user id.
ii. Event name: Login

3. Password reset
a. Only logged in users can perform this update.
b. Accept current password and new password
c. Send to Analytics platform with the payload after reset:
i. User attributes: Email, current timestamp, user id
ii. Event name: Password Reset

d. Send an email for notifying password has been changed
4. Update account details
a. Only logged in user can perform this update
b. Accept name, phone number, address
c. Send to Segment with payload after update:
i. User attributes: email, current timestamp, user id
ii. Event name: Account Updated
d. Send to Klaviyo with payload after update:
i. User attributes: fields that have been updated. Eg: if only name has been
updated, then send name only
ii. Event name: Account Updated

How To:
1. Create your implementation using Laravel (at least version 8) with best practices and OOP
in mind.
2. Use Laravel Event for analytics, observability and messaging services.
3. For analytics, observability and messaging tools, feel free to log it in Laravel logs; we won’t
need you to integrate it for real.
4. Google Analytics payload needs to be JSON format.
5. Please create a repo and share with us during submission
