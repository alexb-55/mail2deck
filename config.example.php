<?php
define("NC_SERVER", "localhost"); // https://server.domain (without "https://" attachments will not be created)
define("NC_USER", "deckbot");
define("NC_PASSWORD", "****"); // if your Nextcloud instance uses Two-Factor-Authentication, use generated token here instead of password.
define("MAIL_SERVER", "localhost"); // server.domain
define("MAIL_SERVER_FLAGS", "/novalidate-cert"); // flags needed to connect to server. Refer to https://www.php.net/manual/en/function.imap-open.php for a list of valid flags.
define("MAIL_SERVER_PORT", "143");
define("MAIL_SERVER_SMTPPORT", "587"); // port for outgoing smtp server. Actually only used to configure Docker image outgoing SMTP Server
define("MAIL_USER", "incoming");
define("MAIL_PASSWORD", "****");
define("DECODE_SPECIAL_CHARACTERS", true); //requires mbstring, if false special characters (like öäüß) won't be displayed correctly
define("ASSIGN_SENDER", true); // if true, sender will be assigned to card if has NC account
define("ASSIGN_ALL_BOARD_USERS", true); // if true, then all users of the current board will be added to the card
define("SET_DUETIME_CARD", "17:55:00"); //Sets the duetime card of the current day. (If you want to disable this function, uncomment this line //)
define("MAIL_NOTIFICATION", true); // if true, send notifications when a new card was created or an error occured
define("DELETE_MAIL_AFTER_PROCESSING", true);
define("NC_DECK_DEFAULT_PREFIX_BOARD", "bot4"); //Prefix to the email addresses from which emails are sent for deckbot, but for some reason the board is not listed in the email. All such emails will be added to this board by default. Example: We send an email from test@test.com with no board name, the prefix is set to "bot4". Create a board with the name "bot4test@test.com".  Grant permission for this board to our user. An email sent without a board name will go to that  board.
define("PREFIX_BOARD_NAME", '@'); //Board name prefix in the subject line
define("POSTFIX_BOARD_NAME", '@'); //Board name postfix in the subject line