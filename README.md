# mail2deck
Provides an "email in" solution for the Nextcloud Deck app

[Docker hub](https://hub.docker.com/r/alexb55/mail2deck)

# 🚀 A. For users
Follow the above steps to add a new card from email.

* Deck Bot is the user who will create the cards and it will be set up by your nextcloud admin.
* In this tutorial email address for Deck Bot will be: <code>bot@ncserver.com</code>

## 1) Assign Deck Bot to the board.
Deck Bot must be assigned and must have edit permission inside the board.

## 2) Mail subject & content
Let's assume you want to add a card with title "Update website logo" on board "Website" and stack "To do".
You can do this in two ways.


### 2.1: Set stack and board in the email subject
Here's how the email subject should look like:
<code>Update website logo b-'website' s-'to do'</code>

* *You can use single or double quotes.*

* *Case-insensitive for board and stack respectively.*

Or for faster and easier entry, specify your prefix and postfix in the config file(For example, specify prefix='@' and postfix='@')
<code>Update website logo @website@ s-'to do'</code>

### 2.2: Set the board in the email address
At the end of the email address prefix (before @) add "+website"

Example: <code>bot+website@ncserver.com</code>

* *If board has multiple words e.g. <code>"some project"</code>, you'll have to send the email to <code>bot+some+project@ncserver.com</code>*

In this case, if you don't specify the stack in the email subject, the card will be added in the first stack (if it exists).

Note:
* Email content will be card description
* You can add attachments in the email and those will be integrated in the created card


### 2.3: Specify assignee

Here's how the email subject should look like:

`Update website logo b-'website' s-'to do' u-'bob'`

* *You can use single or double quotes.*
* *Case-insensitive for board, stack and user respectively.*

Or for faster and easier entry, specify your prefix and postfix in the config file(For example, specify prefix='@' and postfix='@')
<code>Update website logo @website@ s-'to do' u-'bob'</code>

### 2.4: Specify due date
You can use the optional parameter `d-` to add a due date to a card.
Here's how the email subject should look like if you want to set a due date to the card:

`Update website logo b-'website' s-'to do' u-'bob' d-'2022-08-22T19:29:30+00:00'`

* *You can use single or double quotes.*

Or for faster and easier entry, specify your prefix and postfix in the config file(For example, specify prefix='@' and postfix='@')
<code>Update website logo @website@ s-'to do' u-'bob' d-'2022-08-22T19:29:30+00:00'`</code>

If no due data was specified and SET_DUETIME_CARD was commented out, the current date and time will be set, if SET_DUETIME_CARD was uncommented, the execution time set in this parameter, of the current day will be set. 

### 2.5 Set board in the email subject
Here's how the email subject should look like:
<code>Update website logo b-'website'</code>

* *You can use single or double quotes.*

* *Case-insensitive for board and stack respectively.*

Or for faster and easier entry, specify your prefix and postfix in the config file(For example, specify prefix='@' and postfix='@')

<code>`Update website logo @website@'</code>

# ⚙️ B. For NextCloud admins to setup
## Requirements
This app requires php-curl, php-mbstring ,php-imap and some sort of imap server (e.g. Postfix with Courier).
## NC new user
Create a new user from User Management on your NC server, which shall to function as a bot to post cards received as mail. We chose to call it *deckbot*, but you can call it whatever you want.<br>
__Note__: that you have to give *deckbot* permissions on each board you want to add new cards from email.
## Configure Email
### Option 1 - Set up Postfix for incoming email
You can setup Posfix mail server folowing the instructions on [Posfix setup](https://docs.gitlab.com/ee/administration/reply_by_email_postfix_setup.html), and after that add "+" delimiter (which separates the user from the board name in the email address) using the command:<br>
```
sudo postconf -e "recipient_delimiter = +"
```
### Option 2 - Use an existing email server
This could be any hosted email service. The only requirement is that you can connect to it via the IMAP protocol.
*Please note this option may not be as flexible as a self-hosted server. For example your email service may not support the "+"delimiter for directing messages to a specific board.*
## Download and install
### Bare-metal installation
If using a self-hosted Postfix server, clone this repository into the home directory of the *incoming* user. If not self-hosting, you may need to create a new user on your system and adjust the commands in future steps to match that username.<br>
```
su - incoming
git clone https://github.com/alexb55/mail2deck.git mail2deck
```
Create config.php file and edit it for your needs: 
```
cd /home/incoming/mail2deck
cp config.example.php config.php
sudo vim config.php
```
*You can refer to https://www.php.net/manual/en/function.imap-open.php for setting the value of MAIL_SERVER_FLAGS*
#### Add a cronjob to run mail2deck.
```
sudo crontab -u incoming -e
```
Add the following line in the opened file (in this example, it runs every 5 minutes):
<code>*/5 * * * * /usr/bin/php /home/incoming/mail2deck/index.php >/dev/null 2>&1</code>

### Docker installation
### Option 1 - Pull from [Docker Hub](https://hub.docker.com/r/alexb55/mail2deck)
Download image
```
docker pull alexb55/mail2deck
```

Create a file on your host
```
nano config.php
```
To configure it, create a config.php file ([Example](https://hub.docker.com/r/alexb55/mail2deck))


Run the docker image with your DNS and your PATH to the host where the configuration file created above is located. 
```
docker run --dns=192.168.1.1 -d --network host --name mail2deck  -v /YOUR_PATH/config.php:/home/deckbot/mail2deck/config.php alexb55/mail2deck:latest
```

Edit your crontab
```
crontab -e
```

And add this line
```
*/1 * * * *  docker start mail2deck
```

Finish

Now __mail2deck__ will add new cards every one minutes if new emails are received.


### Option 2 - Clone, Build, Run
Clone and edit the config.example.php you find in this repository and move it as config.php
```
git clone https://github.com/alexb55/mail2deck.git mail2deck
cd mail2deck
cp config.example.php config.php
nano config.php
```

Build your image locally
```
docker build -t mail2deck:latest .
```

Run the docker image mapping the config.json as volume
```
docker run -d --name mail2deck mail2deck:latest
```

Edit your crontab
```
crontab -e
```

And add this line
```
*/5 * * * *  /usr/bin/docker start mail2deck
```

## Finish
Now __mail2deck__ will add new cards every five minutes if new emails are received.

---
## Creating a configuration file
1)  To configure it, create a config.php file with the following content:
```php
<?php
define("NC_SERVER", "https://NC_Server.domain"); // https://server.domain (without "https://" attachments will not be created)
define("NC_USER", "deckbot"); //A user in NextCloud who will add cards from the mail. (Must be added to the board where you want to add new cards from email)
define("NC_PASSWORD", "****"); // if your Nextcloud instance uses Two-Factor-Authentication, use generated token here instead of password.
define("MAIL_SERVER", "imap.gmail.com"); // server.domain
define("MAIL_SERVER_FLAGS", "/ssl"); // flags needed to connect to server. Refer to https://www.php.net/manual/en/function.imap-open.php for a list of valid flags.
define("MAIL_SERVER_PORT", "993");
define("MAIL_SERVER_SMTPPORT", "465"); // port for outgoing smtp server. Actually only used to configure Docker image outgoing SMTP Server
define("MAIL_USER", "YOUR_MAIL@gamil.com");
define("MAIL_PASSWORD", "****");
define("DECODE_SPECIAL_CHARACTERS", true); //requires mbstring, if false special characters (like öäüß) won't be displayed correctly
define("ASSIGN_SENDER", true); // if true, sender will be assigned to card if has NC account
define("ASSIGN_ALL_BOARD_USERS", true); // if true, then all users of the current board will be added to the card
define("SET_DUETIME_CARD", "17:55:00"); //Sets the duetime card of the current day. (If you want to disable this function, uncomment this line //)
define("MAIL_NOTIFICATION", false); // if true, send notifications when a new card was created or an error occured
define("DELETE_MAIL_AFTER_PROCESSING", true);
define("NC_DECK_DEFAULT_PREFIX_BOARD", "bot4"); //Prefix to the email addresses from which emails are sent for deckbot, but for some reason the board is not listed in the email. All such emails will be added to this board by default. Example: We send an email from test@test.com with no board name, the prefix is set to "bot4". Create a board with the name "bot4test@test.com".  Grant permission for this board to our user. An email sent without a board name will go to that  board.
define("PREFIX_BOARD_NAME", '@'); //Board name prefix in the subject line
define("POSTFIX_BOARD_NAME", '@'); //Board name postfix in the subject line
```
  
  
<p>2) Change the values in the config.php file you created to your data  

## Installation in the docker
<p>1) Run the docker image with your DNS and your PATH to the host where the configuration file created above is located.

Example:
```
docker run --dns=192.168.1.1 -d --network host --name mail2deck  -v /YOUR_PATH/config.php:/home/deckbot/mail2deck/config.php alexb55/mail2deck:latest  
```

<p>2) Start the container

```
docker start mail2deck
```

---

## Or install mail2deck on TrueNas Cobia (Step 1 and 2 above to perform)

1)
![TrueNAS Docker Settings](https://github.com/alexb-55/mail2deck/assets/125076966/91d25110-0a10-4fa5-8310-39f13af1c9eb)

![TrueNAS Docker Settings2](https://github.com/alexb-55/mail2deck/assets/125076966/defe617d-1de4-49ef-8628-f862da71fdbf)

2) Install ([heavy_script](https://github.com/Heavybullets8/heavy_script))
   
3) Create Cron Job (System Settings -> Advanced -> Cron Jobs -> Add)
   


![TrueNas Docker Cron Settings](https://github.com/alexb-55/mail2deck/assets/125076966/42594356-f30b-4b63-b007-e936ec79e76f)


```
bash /root/heavy_script/heavy_script.sh app -s mail2deck
```
4) Finish!
