<?php

namespace Mail2Deck;

class MailClass {
    private $inbox;

    public function __construct()
    {
        $this->inbox = imap_open("{" . MAIL_SERVER . ":" . MAIL_SERVER_PORT . MAIL_SERVER_FLAGS . "}INBOX", MAIL_USER, MAIL_PASSWORD)
        or die("can't connect:" . imap_last_error());
    }

    public function __destruct()
    {
        imap_close($this->inbox);
    }

    public function getNewMessages() {
        return imap_search($this->inbox, 'UNSEEN');
    }

    public function fetchMessageStructure($email) {
        return imap_fetchstructure($this->inbox, $email);
    }

    public function fetchMessageBody($email, $section) {
        return imap_fetchbody($this->inbox, $email, $section);
    }

    public function fetchMessageBody2($email) {
        $body = $this->get_part($this->inbox, imap_uid($this->inbox, $email), "TEXT/HTML");
        // if PLAIN body is empty, try getting HTML
        if ($body == "") {
          $body = $this->get_part($this->inbox, imap_uid($this->inbox, $email), "TEXT/PLAIN");
        }
        return $body;
      }

    function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false) {
        if (!$structure) {
           $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                return $this->_encodeText(imap_fetchbody($imap, $uid, $partNumber, FT_UID), $structure->encoding);
            }
 
            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = $this->get_part($imap, $uid, $mimetype, $subStruct, $prefix. ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    function get_mime_type($structure) {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
 
        if ($structure->subtype) {
           return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    public function headerInfo($email) {
        $headerInfo = imap_headerinfo($this->inbox, $email);
        $additionalHeaderInfo = imap_fetchheader($this->inbox, $email);
        $infos = explode("\n", $additionalHeaderInfo);

        foreach($infos as $info) {
            $data = explode(":", $info);
            if( count($data) == 2 && !isset($head[$data[0]])) {
                if(trim($data[0]) === 'X-Original-To') {
                    $headerInfo->{'X-Original-To'} = trim($data[1]);
                    break;
                }
            }
        }

        return $headerInfo;
    }

    public function _encodeText($text, $type) {
        if ($type == ENC7BIT) {#0
                //return  mb_convert_encoding($text, "UTF-8", "auto");
                return  $text;
        } else if ($type == ENC8BIT) {#1
                //return imap_8bit($text);
                return quoted_printable_decode(imap_8bit($text));
        } else if ($type == ENCBINARY) {#2
                //return imap_base64(imap_binary($text));
                return imap_binary($text);
        } else if ($type == ENCBASE64) {#3
                return imap_base64($text);
        } else if ($type == ENCQUOTEDPRINTABLE) {#4
                return imap_qprint($text);
                //return quoted_printable_decode($text);
        } else if ($type == ENCOTHER) {#5
                return  $text;
        } else {#UNKNOW
                //return trim(utf8_encode(quoted_printable_decode(imap_qprint($text))));
                //return imap_qprint($text);
                return $text;
        }
    }

    public function reply($sender, $response = null) {
        $server = NC_SERVER;

        if(strstr($server, "https://")) {
            $server = str_replace('https://', '', $server);
        } else if(strstr($server, "http://")) {
            $server = str_replace('http://', '', $server);
        }

        $headers = array(
            'From' => 'no-reply@' . $server,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html'
        );

        if($response) {
            $body = "<h1>A new card has been created on board <a href=\"" . NC_SERVER . "/index.php/apps/deck/#/board/{$response->board}" . "\">{$response->boardTitle}</a>.</h1>
                    <p>Check out this <a href=\"" . NC_SERVER . "/index.php/apps/deck/#/board/{$response->board}/card/{$response->id}" . "\">link</a> to see the newly created card.</p>
                    <p>Card ID is {$response->id}</p>";
            $subject = 'A new card has been created!';
        } else {
            $body = "<h1>There was a problem creating a new card.</h1><p>Make sure the board was setup correctly.</p>";
            $subject = "A new card could not be created!";
        }

        $message = "<html>";
        $message .= "<head><title>mail2deck response</title></head>";
        $message .= "<body>$body</body>";
        $message .= "</html>";

        mail($sender, $subject, $message, $headers);
    }

    /**
     * Mark emails for deletion
     * 
     * @param $email email number that you want to delete
     * 
     * @return void
     */
    public function delete(int $email)
    {
        imap_delete($this->inbox, imap_uid($this->inbox, $email), FT_UID);
    }

    /**
     * Delete all messages marked for deletion
     */
    public function expunge()
    {
        imap_expunge($this->inbox);
    }
}
