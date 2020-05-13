
# UT Quest Assignments Calendar Downloader
This script uploads your homework/assignments due dates to Google Calendar and sends a notification whenever a new assignment is uploaded or extended on the University of Texas Quest board (quest.cns.utexas.edu). Last time this was tested was on the end of the 2016 Spring semester.


## Why?
This was created for three reasons: Quest, the platform that most universities and colleges use, does not have a mobile app, their website is not mobile-friendly, and does not synchronize with calendar platforms such as iCalendar, Google Calendar.. etc.

## Requirements
 - PHP 5.6 or higher
 - OpenSSL Extension 
 - Pushbullet API PHP Library
 - Google Calendar API PHP Library
 - New Calendar in Google Calendar (calendar.google.com)

## What does this script do?

 1. The script logs in on your behalf and downloads the Assignments lists along with their due dates.
 2. Checks if the assignment already exists in your Google Calendar.
	 2a. If the assignment exists, it updates the calendar entry with the new due date & time.
	 2b. If the assignment is new, it creates a new calendar entry.
3. Pushs a new Pushbullet Note to the Homework Channel with the info mentioned above.

## How does it work?

 - You will need to set up a new Google Calendar project by going to  https://console.developers.google.com/apis/library/calendar-json.googleapis.com
	 1. Create a project
	 2. Enter a project name
	 3. Configure consent screen
	 4. Select on External and Create
	 5. Enter Application Name and Save
	 6. Search for Google Calendar API and click Enable
	 7. Visit [https://console.developers.google.com/apis/credentials](https://console.developers.google.com/apis/credentials) and click Create credentials > OAuth Client ID
	 8. Check Web application for Application Type
	 9. Go back to the PHP file and copy Client ID, Client ID secret, API Key into it. 
 - You will need a Pushbullet Access Token (API Key). That can be retrieved by going to https://www.pushbullet.com/#settings/account and click on 'Create Access Token.'
 - You will need to create a new Pushbullet Channel. By pushing notes to one channel, you keep all notifications organized and can be shared with other classmates.
 - You will need your UT EID, password, and course ID.

**THE END. Thanks for coming!**

Any suggestions, questions, comments, feedback send them to my email address: [my_username@live.com](mailto:my_username@live.com)
