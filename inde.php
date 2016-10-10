<?php
ob_start();
define('API_KEY','295804730:AAG_CNVIQWDErlZaok8KmhGsD-bQmiYmWfI');
$the_admin_id = 249010980;

file_put_contents("count",file_get_contents("count")+1);

$ad_btn='Free training program';
$ad_url='https://telegram.me/joinchat/DtebJD-YicabzaggOWIHeQ';
$ad_text = "Text";

function getUserLocation($uid,$default){
    $cached = apc_fetch('location-'.$uid);
    return $cached?$cached:$default;
}

function getUserStep($uid,$default){
    $cached = apc_fetch('step-'.$uid);
    return $cached?$cached:$default;
}

function setUserStep($uid,$step){
    apc_store('step-'.$uid,$step,60*60*12);
}

function setUserLocation($uid,$location){
    apc_store('location-'.$uid,$location,60*60*12);
}

function check_has_string($what,$base){
    return str_replace($what,"",$base) != $base;
}

function is_valid_url($url){
    preg_match("'^https://telegram.me/joinchat/[A-Za-z-_0-9]+'si",$url,$m1);
    preg_match("'^http://telegram.me/joinchat/[A-Za-z-_0-9]+'si",$url,$m2);
    return (count($m1)>0 || count($m2) > 0);
}


function is_url($uri){
    if(preg_match( '/^(http|https):\\/\\/[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-zآ-ی]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
        return $uri;
    }
    else{
        return false;
    }
}



function is_valid_url_international($url){
    return is_url($url);
}

class ListNoVia{

    static function saveListCode($userid,$code){
        apc_store('novia_list_'.$userid,$code,60*60*12);
    }

    static function getListCode($userid){
        return apc_fetch('novia_list_'.$userid);
    }

    static function saveChannelID($userid,$code){
        apc_store('novia_chid_'.$userid,$code,60*60*12);
    }

    static function getChannelID($userid){
        return apc_fetch('novia_chid_'.$userid);
    }

}

class ListTab{
    static function makeMakeID(){
        return md5(time().rand(100000,999999));
    }

    static function setCurrentMakeID($uid,$mid){
        apc_store('make_list_id-'.$uid,$mid,60*60*12);
    }

    static  function getCurrentMakeID($uid){
        return apc_fetch('make_list_id-'.$uid);
    }

    static function updateDB($mid,$newUpdate){
        $file = __DIR__.'/tabs/list-'.$mid.'.json';
        file_put_contents($file,json_encode($newUpdate));
    }

    static function getMakeDB($mid,$o = true){
        $file = __DIR__.'/tabs/list-'.$mid.'.json';
        if(file_exists($file)){
            return json_decode(file_get_contents($file));
        }else{
            if($o)  file_put_contents($file,'{}');
            return json_decode('{}');
        }
    }
}

function makeMakeID(){
    return md5(time().rand(100000,9999999));
}

function setCurrentMakeID($uid,$mid){
    apc_store('make_id-'.$uid,$mid,60*60*12);
}

function getCurrentMakeID($uid){
    return apc_fetch('make_id-'.$uid);
}

function updateDB($mid,$newUpdate){
    $file = __DIR__.'/tabs/tab-'.$mid.'.json';
    file_put_contents($file,json_encode($newUpdate));
}

function getMakeDB($mid,$o = true){
    $file = __DIR__.'/tabs/tab-'.$mid.'.json';
    if(file_exists($file)){
        return json_decode(file_get_contents($file));
    }else{
        if($o)  file_put_contents($file,'{}');
        return json_decode('{}');
    }
}


function makeHTTPRequest($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}

function makeHTTPRequestAPI($method,$datas=[],$API){
    $url = "https://api.telegram.org/bot".$API."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}




// Fetching UPDATE
$update = json_decode(file_get_contents('php://input'));

var_dump($update);


if(isset($update->callback_query)){

}elseif(isset($update->inline_query)){
$userID = $update->inline_query->from->id;    
echo 'QUERY ...';
    $theQuery = $update->inline_query->query;
    if(str_replace('getlist-','',$theQuery) != $theQuery ){
        $theListId = trim(str_replace('getlist-','',$theQuery));
        $DB = ListTab::getMakeDB($theListId,false);
        if(isset($DB->count) && $DB->count > 0){
            $btns = [];
            foreach($DB->list as $button){
                $button->url =
                    str_replace("\n","",trim($button->url," \t\n\r\0\x0B."));
                array_push($btns,[(array)$button]);
            }
            var_dump( $DB->list);
            var_dump($btns);
            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'cache_time'=>1,
                'results' => json_encode([[
                    'type' => 'article',
                    'id' => base64_encode(1),
                    'title' => 'Click to display list',
                    'input_message_content' => ['parse_mode' => 'HTML', 'message_text' => $DB->text],
                    'reply_markup' => [
                        'inline_keyboard' => $btns                   ]
                ],
                    [
                        'type'=>'article',
                        'id'=>base64_encode(rand(5,555)),
                        'title'=>'Click to write code',
                        'input_message_content'=>[
                            'message_text'=>'@Paquabot getlist-'.$theListId
                        ]
                    ]])
            ]));
        }else{
            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'results' => json_encode([]),
                'switch_pm_text'=>'List not found',
                'switch_pm_parameter'=>'newlist'
            ]));
        }

    }elseif(str_replace('getbanner-','',$theQuery) != $theQuery ){
        $theTabId = trim(str_replace('getbanner-','',$theQuery));
        $DB = getMakeDB($theTabId,false);
        if(isset($DB->list) || isset($DB->count)){
            $btns = [];
            foreach($DB->list as $button) {
		$button->text=$button->text;
                $button->url =
                    str_replace(["\n","‌"," "],["", "",""], trim($button->url));
                array_push($btns, [(array)$button]);
            }


            var_dump(makeHTTPRequest('answerInlineQuery', [
                'inline_query_id' => $update->inline_query->id,
                'cache_time' => 1,
                'results' => json_encode([[
                    'type' => 'photo',
                    'id' => base64_encode(rand(300, 400)),
                    'photo_file_id' => $DB->f->image_id,
                    'caption' =>  $DB->f->text,
                    'reply_markup' => [
                        'inline_keyboard' => $btns
                    ]
                ]
//
                ])
            ]));
            if($update->inline_query->from->id == $the_admin_id){
                var_dump($DB);
                file_put_contents('ola2',ob_get_clean());
            }
        }else {
            file_put_contents('me',ob_get_clean());
            if (isset($DB->done)) {
                if (str_replace('developer', '', $DB->f->image_id) != $DB->f->image_id) {
                    var_dump(makeHTTPRequest('answerInlineQuery', [
                        'inline_query_id' => $update->inline_query->id,
                        'cache_time' => 1,
                        'results' => json_encode([
                            [
                                'type' => 'photo',
                                'id' => base64_encode(3),
                                'photo_url' => $DB->f->image_id,
                                'thumb_url' => $DB->f->image_id
                                , 'reply_markup' => ['inline_keyboard' => [
                                [
                                    ['text' => 'Click to view', 'url' => $DB->f->join]
                                ]]
                            ]
                            ]
                        ]),
                    ]));

                    file_put_contents('me', ob_get_clean());
                } else {
                    var_dump(makeHTTPRequest('answerInlineQuery', [
                        'inline_query_id' => $update->inline_query->id,
                        'cache_time' => 1,
                        'results' => json_encode([[
                            'type' => 'photo',
                            'id' => base64_encode(rand(300, 400)),
                            'photo_file_id' => $DB->f->image_id,
                            'caption' => $DB->f->text,
                            'reply_markup' => ['inline_keyboard' => [
                                [
                                    ['text' => 'Click to view', 'url' => $DB->f->join]
                                ]]
                            ]
                        ],
//                    [
//                        'type' => 'photo',
//                        'id' => base64_encode(rand(100,500)),
//                        'title' => $DB->s->chid,
//                        'photo_url' => $DB->s->image_id,
//                        'thumb_url' => $DB->s->image_id,
//                        'caption'=>$DB->s->text,
//                        'reply_markup'=>[ 'inline_keyboard'=>[
//                            [
//                                ['text'=>'برای عضویت کلیک کنید','url'=>$DB->s->join]
//                            ]]
//                        ]
//                    ]
                        ])
                    ]));
                }

            } else {

                var_dump(makeHTTPRequest('answerInlineQuery', [
                    'inline_query_id' => $update->inline_query->id,
                    'results' => json_encode([])
                ]));
            }
        }
    }elseif($theQuery == 'Exchanges') {

    }else{
        var_dump(makeHTTPRequest('answerInlineQuery', [
            'inline_query_id' => $update->inline_query->id,
            'results' => json_encode([]),
		'switch_pm_text'=>'New',
'switch_pm_parameter'=>'new'
        ]));
    }

if( $update->inline_query->from->id  == $the_admin_id){
file_put_contents('ola',ob_get_clean());
}

}else{
    var_dump([
        getUserStep($userID,false),getUserLocation($usgerID,false)
    ]);
    $userID = $update->message->from->id;

    $file_o = __DIR__.'/users/'.$userID.'.json';
    file_put_contents($file_o,json_encode($update->message->from));

    $userTEXT = isset($update->message->text)?$update->message->text:'';
    $currentLocation = getUserLocation($userID,'home');
    $currentStep = getUserStep($userID,1);
    var_dump([
        'Location'=>$currentLocation,
        'Step'=>$currentStep
    ]);
    //analysing the message

    if($userTEXT == '/newtab'){
        setUserLocation($userID,'make');
        setUserStep($userID,1);
    }

    if($userTEXT == '/convert'){
        setUserLocation($userID,'convert');
        setUserStep($userID,1);
    }

    if($userTEXT == '/submit'){

        if(getUserLocation($userID,'home') == 'make'){
            if(getUserStep($userID,'1') == '4'){
                setUserStep($userID,'6');
            }else{
                makeHTTPRequest('sendMessage',[
                    'chat_id'=>$userID,
                    'text'=>"❗️ society, you still did not complete the Liste! \n To Cancel on /cancel Click"
                ]);
                die;
            }
        }
        elseif(
        in_array(getUserLocation($userID,'home'),['list','convert'])){
            if(getUserStep($userID,'1') == '3'){
                setUserStep($userID,'5');
            }else{
                makeHTTPRequest('sendMessage',[
                    'chat_id'=>$userID,
                    'text'=>"❗️ society, you still did not complete the Liste! \n To Cancel on /cancel Click"
                ]);
                die;
            }
        }

    }

    if($userTEXT == '/newlist'){
        setUserLocation($userID,'list');
        setUserStep($userID,'1');
    }

    if($userTEXT == '/cancel'){
        setUserLocation($userID,'home');
        setUserStep($userID,1);
    }

    if($userTEXT == "/help"){
        $helpTXT = "Welcome to guide the robot 🌐 👍
➖➖➖➖➖➖➖

Full training video:

The robot will allow you to see your channels and click a banner with glass application.

🌐🌐! ️ got new update, you can exchange lists with buttons Glass implement
Finally Description Training

You must first robot to use on /newtab click.
Then the robot asks you a series of questions and information you need to enter your channel.

After finishing robot will give you a code like the following code .👇👇

<pre> @Paquabot d6cdbea45b238632bdd6d11dcf7fe98f </pre>

Copy this code and any time you want to display your banner (or exchange to another channel administrator manager) of the code.

Use the code in this way is that you paste it in the field related to Chat (Robot inline is like all the robots and inline) and wait until your banner is loaded. Then click it.

The image below is an example: 👇👇👇👇

»» Training disassemble exchange list

First Command / newlist button.

Robot lists the text you want, below this text will be buttons for example (the most recent IT channel ...)

Now to the title and link channels to the robot manager.

Each time you press Enter on last link / submit button to your list ready.

"Convert exchange list to list Glass

In the first stage exchange text (text that comes the list below) to the robot. Now you have to give a list of buttons.

To do this you must create a list in the following format:

The robot gives you a code.

Gives you the code for the robot as in the image below.";
        $imageID = 'AgADBAADQ6oxG_sS0QWIeBEDRy1gg9sVQxkABPxnx4JtVvzC6j4CAAEC';
        makeHTTPRequest('sendMessage',[
            'chat_id'=>$userID,
            'text'=>$helpTXT,
            'parse_mode'=>'HTML'
        ]);
        makeHTTPRequest('sendPhoto',[
            'chat_id'=>$userID,
            'photo'=>$imageID,
            'caption'=>'First, wait until the list is loaded.'
        ]);
        die;
    }

    switch($currentLocation){
        case 'home':{

        }
    }

    if($userTEXT == '/novia'){
        setUserLocation($userID,'novia');
        setUserStep($userID,'1');
    }

    $currentLocation = getUserLocation($userID,'home');
    $currentStep = getUserStep($userID,'1');

    $cancel_Text = "\n ➖➖➖➖➖➖➖➖➖ \nfor cancellation on /cancel button";
    //action
    switch($currentLocation){

        case 'novia':{

            switch($currentStep){

                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'text'=>"Welcome to the No Via 🌐.
➖➖➖➖➖➖

What to do whatever I need to know this via Paquabot!
Our robot does not need admin channel, group or anything to send banner. We do this through a Inline of our Anham.

Telegram good to show you what your robot via (the device) displays.

You must be a registered @botfather and bot robot Fadr gives you a token.

This token save it. Then the robot to the admin channel (robot that you made)

Inline code like:
<code> @Paquabot getlist-XXXXX </code>

👈 Now send me your inline code (currently only lists acceptable)".$cancel_Text,
                        'parse_mode'=>"HTML",
                        'chat_id'=>$userID
                    ]);
                    setUserStep($userID,'2');
                }break;

                case '2':{
                    $code = $update->message->text;
                    if(str_replace('@Paquabot getlist-','',$code) != $code){
                        $code_list = trim(str_replace('@Paquabot getlist-','',$code));
                        $DB = getMakeDB($code_list,false);
                        if(isset($DB->done)){
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"! ️ structure your code, but it just was not registered in the database.

Please create a new list /newlist own.".$cancel_Text,
                                'parse_mode'=>'HTML'
                            ]);
                        }else{
                            ListNoVia::saveListCode($userID,$code_list);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"✅ your code correctly diagnosed.

Now you need to give your channel id.

Note that the robot must have administrator channel and id with @ to send.

For example @WathiqApi

This section is mandatory.".$cancel_Text,
                                'parse_mode'=>'HTML'
                            ]);
                            setUserStep($userID,'3');
                        }
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗️ your code is unacceptable.

Your code should look like this:
<code> @Paquabot getlist-XXXXX </code>

Note Only the codes are accepted List.".$cancel_Text,
                            'parse_mode'=>'HTML'
                        ]);
                    }
                }break;

                case '3':{
                    $m = 'ID ✅
Now your robot token in @botfather manager.
Tokens are in the form of
<code> 1111111: AAAAA ... </code>
are.';
                    $channel_id = $update->message->text;
                    if(str_replace('@','',$channel_id) != $channel_id){
                        ListNoVia::saveChannelID($userID,strtolower(trim($channel_id)));
                        setUserStep($userID,'4');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>$m.$cancel_Text,
                            'parse_mode'=>'HTML'
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗Shnas·h wrong channel.

ID must have an @.".$cancel_Text
                        ]);
                    }
                }break;

                case '4':{
                    $botToken = $update->message->text;
                    if(str_replace(':','',$botToken) != $botToken){
                        $list_id = ListNoVia::getListCode($userID);
                        $DB = ListTab::getMakeDB($list_id,false);
                        var_dump($DB);
                        var_dump($ListID);
                        if(isset($DB->count) && $DB->count > 0) {
                            echo "YEA/\n";
                            $btns = [];
                            foreach ($DB->list as $button) {
                                $button->url =
                                    str_replace("\n", "", trim($button->url));
                                array_push($btns, [(array)$button]);
                            }
                            $result = makeHTTPRequestAPI('sendMessage',[
                                'text'=>$DB->text,
                                'chat_id'=>ListNoVia::getChannelID($userID),
                                'parse_mode'=>'HTML',
                                'reply_markup'=>json_encode([
                                    'inline_keyboard'=>$btns
                                ])
                            ],$botToken);
                            var_dump($result);
                            if($result->ok == true){
                                makeHTTPRequest('sendMessage',[
                                    'text'=>"Your message was successfully sent ✅ $channel_id",
                                    'chat_id'=>$userID
                                ]);
                                makeHTTPRequest('sendMessage',[
                                    'chat_id'=>$update->message->from->id,
                                    'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                                    'parse_mode'=>"HTML",
                                    'disable_web_page_preview'=>true
                                ]);
                            }else{
                                makeHTTPRequest('sendMessage',[
                                    'text'=>"❗Mtasfanh message could not be sent.

Can be one of the reasons involved.

1. ID is the wrong channel
2. You write api incorrectly.
3. Robot is not a channel administrator.
".$cancel_Text,
                                    'chat_id'=>$userID
                                ]);
                            }
                        }else{
                            echo "NOA\n";
                            var_dump(makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"Your list has trouble \nPlease /newslist implement new listings."
                            ]));
                            setUserLocation($userID,'home');
                        }

                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"❗️ robot token you think is wrong.
Tokens are in the form of
<code> 1111111: AAAAA ... </code>
are.",
                            'parse_mode'=>'HTML'
                        ]);
                    }

                    setUserStep($userID,'1');
                    setUserLocation($userID,'home');
                }break;


            }


        }break;


        case 'convert':{

            switch($currentStep){

                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"Welcome to the section list.\n First well text you. This text is placed below it.".$cancel_Text
                    ]);
                    setUserStep($userID,'2');
                    ListTab::setCurrentMakeID($userID,ListTab::makeMakeID());
                    $ListID = ListTab::getCurrentMakeID($userID);
                    $DB = ListTab::getMakeDB($listID);
                    $DB->list = json_decode('[]');
                    $DB->count=0;
                    ListTab::updateDB($listID,$DB);
                }break;

                case '2':{
                    $text =$update->message->text;
                    if(mb_strlen($text) < 5){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'The text must be at least 3 characters'.$cancel_Text
                        ]);
                    }else {
                        $ListID = ListTab::getCurrentMakeID($userID);
                        $DB = ListTab::getMakeDB($ListID);

                        $DB->text = $text;
                        $DB->list = 'n';
                        ListTab::updateDB($ListID, $DB);
                        var_dump('The DATABASE');
                        var_dump($DB);

                        makeHTTPRequest('sendMessage', [
                            'chat_id' => $userID,
                            'text' => '✅ text was recorded.

Now you have a list in the form below to send us:

The first button text
https://telegram.me/WathiqApi
The second button text
https://telegram.me/love4e


Respectively, in each separate line of text and press the submit button link:' . $cancel_Text
                        ]);
                        setUserStep($userID, '3');
                    }

                }break;


                case '3':{
                    $list = $update->message->text;
                    $list = preg_replace("/(\n)+/","\n",trim($list));
                    $explode = explode("\n",$list);
                    $nList = "".$cancel_Text;
                    if(count($explode) % 2 != 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>$nList
                        ]);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"Free training program
https://telegram.me/
Robot development training Telegram
https://telegram.me/
Check out our robots
https://telegram.me/"
                        ]);
                    }else{
                        $newList = [];
                        $valid_url = true;
                        foreach($explode as $key=>$title){
                            if($key % 2 == 0){
                                $newList[($key/2)]=["text"=>trim($title)];
                            }else{
                                echo $key."\n";
                            }
                        }
                        $i = 0;
                        foreach($explode as $key=>$url){
                            if($key % 2 == 1){
                                $valid_url = is_valid_url_international(str_replace(' ','',trim($url)));
                                $newList[$i]['url'] = str_replace(' ','',trim($url));
                                $i++;
                            }
                        }

                        if($valid_url){
                            var_dump($newList);
                            $ListID = ListTab::getCurrentMakeID($userID);
                            $DB = ListTab::getMakeDB($ListID);
                            var_dump($ListID);
                            $DB->list = $newList;
                            $DB->count = count($newList);
                            ListTab::updateDB($ListID, $DB);
                            $userID = $update->message->from->id;
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>'Congratulations on your list was prepared 👍

Well, I gave you a code, the code inline code that robots called it inline (like me!) They support.

You do not need me to the admin channel.

The code track now (but not write) Wait a list that opens up to you.
Click method when loaded.

If the bad explained /help button is now detailed tips!'
                            ]);

                            $theCommand = '@Paquabot getlist-'.ListTab::getCurrentMakeID($update->message->from->id);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>$theCommand,
                                'reply_markup'=>json_encode([
                                    'inline_keyboard'=>[
                                        [
                                            ['text'=>'Send considered to chat','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                        ]
                                    ]
                                ])
                            ]);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$update->message->from->id,
                                'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                                'parse_mode'=>"HTML",
                                'disable_web_page_preview'=>true
                            ]);

                            setUserLocation($update->message->from->id,'home');
                            setUserStep($update->message->from->id,'3');








                        }else{
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>$nList
                            ]);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"Free training program \nhttps: //telegram.me/iluli \ntraining robot development telegram \nhttps: //telegram.me/WathiqApi \n to check our robot \nhttps://telegram.me/Paquabot"
                            ]);
                        }

                    }
                }break;


            }


        }break;

        case 'list':{
            switch($currentStep){
                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"Welcome 🌐 section lists 👍

Please enter the text.
This text buttons are listed below.".$cancel_Text
                    ]);
                    setUserStep($userID,'2');
                    ListTab::setCurrentMakeID($userID,ListTab::makeMakeID());
                    $ListID = ListTab::getCurrentMakeID($userID);
                    $DB = ListTab::getMakeDB($listID);
                    $DB->list = json_decode('[]');
                    $DB->count=0;
                    ListTab::updateDB($listID,$DB);
                }break;

                case '2':{
                    $text = $update->message->text;
                    if(mb_strlen($text) < 5){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'The text must be at least 3 characters'.$cancel_Text
                        ]);
                    }else{
                        $ListID = ListTab::getCurrentMakeID($userID);
                        $DB = ListTab::getMakeDB($ListID);

                        $DB->text = $text;
                        $DB->list='n';
                        ListTab::updateDB($ListID,$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);

                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'✅ text was recorded.

Now I order you to take the text and link buttons.
When you enter a list of your mind on /submit a Click.

If you have a problem came up in your list /cancel out and re-build.
❗️ careful!

It's time to get started, first in the send button:'.$cancel_Text
                        ]);
                        setUserStep($userID,'3');
                    }
                }break;

                case '3':{
                    $text = $update->message->text;
                    if(mb_strlen($text) > 100){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️ too much text! 😊 should be less than 100 characters'.$cancel_Text
                        ]);
                    }elseif(mb_strlen(trim($text)) == 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️! ️ community! Good one thing 😁 send a blank space or not acceptable'.$cancel_Text
                        ]);
                    }else{
                        var_dump('The DATABASE');
                        var_dump($DB);
                        $DB = ListTab::getMakeDB(ListTab::getCurrentMakeID($userID));
                        if($DB->list == 'n') $DB->list = [];
                        $newObject = json_decode('{}');
                        $newObject->text = $text;
                        array_push($DB->list,$newObject);
                        $DB->count++;
                        $index = count(((array) $DB->list));
                        ListTab::updateDB(ListTab::getCurrentMakeID($userID),$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ Well done! Text button number {$index} Kurdish records.

Now you have a valid link him write it.

Link could be any link (link period, or even link channel link your site) but must first be careful http:// or https:// Put
Make sure the address is correct.
😜".$cancel_Text
                        ]);
                        setUserStep($userID,'4');
                    }
                }break;


                case '4':{
                    $link = $update->message->text;
                    if(is_valid_url_international($link)){
                        $DB = ListTab::getMakeDB(ListTab::getCurrentMakeID($userID));
                        $list = (array) $DB->list;
                        echo "The LIST \n";
                        var_dump($list);
                        $lastID = max(array_keys($list));
                        $lastObject = end($list);
                        $lastObject->url = trim($link);
                        $list[$lastID] = $lastObject;
                        $DB->list = $list;
                        $DB->count++;
//                        var_dump($DB);
//                        $index = $DB->count;
//                        $object = end($DB->list);
//                        var_dump($object);
//                        $object->url = $link;
//                        var_dump("new link ... \n");
//                        var_dump($object);
//                        $DB->list[($index-1)] = $object;
                        $index = count($list);
                        ListTab::updateDB(ListTab::getCurrentMakeID($userID),$DB);
                        echo "THE DATABASE \n";
                        var_dump($DB);
                        setUserStep($userID,'3');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ Evil! Link button {$index} was recorded.

Nga now inkjet buttons now complete. If you want to make Mtnsh's new press send.

But if the hotlink button to complete /submit Click 😊".$cancel_Text,
                            'reply_markup'=>[
                                'keyboard'=>[
                                    [
                                        ['text'=>'/submit'],['text'=>'/cancel']
                                    ]
                                ]
                            ]
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"! ️ robot diagnosed the wrong link!
I guess I https:// with http:// first killed all 😜".$cancel_Text
                        ]);
                    }
                }break;

                case '5':{
                    $userID = $update->message->from->id;
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>'Congratulations on your list was prepared 👍

Well, I gave you a code, the code inline code that robots called it inline (like me!) They support.

You do not need me to the admin channel.

The code track now (but not write) Wait a list that opens up to you.
Click method when loaded.

If the bad explained /help button is now detailed tips!'
                    ]);

                    $theCommand = '@Paquabot getlist-'.ListTab::getCurrentMakeID($update->message->from->id);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$theCommand,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [
                                    ['text'=>'Send considered to chat','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                ]
                            ]
                        ])
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                        'parse_mode'=>"HTML",
                        'disable_web_page_preview'=>true
                    ]);

                    setUserLocation($update->message->from->id,'home');
                    setUserStep($update->message->from->id,'3');
                }break;
            }
        }break;
        case 'make':{
            switch($currentStep){
                case '1':{
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$userID,
                        'text'=>"🖼 Please enter your channel image \nto stop, employee /cancel Enter"
                    ]);
                    setCurrentMakeID($userID,makeMakeID());
                    setUserStep($userID,'2');
                }break;

                case '2':{
                    var_dump($update->message);
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $image_id = isset(end($update->message->photo)->file_id)?end($update->message->photo)->file_id:false;
                    if($image_id === false){
                        makeHTTPRequest('sendMessage',[
                            'text'=>"Please send jpg file \nfile is wrong \nto opt-out, command /cancel Enter",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        if(true) {
                            if (!isset($DB->f)) $DB->f = json_decode("{}");
                            $DB->f->image_id = $image_id;
                            updateDB(getCurrentMakeID($userID), $DB);
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "Below is the text of which was ⌨ Please submit \n text must be less than 300 characters long \n to cancel, command /cancel Enter"
                            ]);
                            setUserStep($userID, '3');
                        }else{
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "Please send jpg file \n $file_ext file has been sent to you. \n <a href='http://image.online-convert.com/convert-to-jpg'> click online to become </a> \n to opt-out, command /cancel Enter",
                                'parse_mode'=>"HTML"
                            ]);
                        }
                    }
                }break;

                case '3':{
                    echo 'Len is '.mb_strlen($update->message->text);
                    if(mb_strlen($update->message->text) > 300){
                        makeHTTPRequest('sendMessage', [
                            'text' => "Text messages you".mb_strlen($update->message->text)."Characters. Please send less than 300 characters. \n To opt-out, command /cancel Enter",
                            'chat_id' => $userID
                        ]);
                    }else {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->f->text = $userTEXT;
                        $DB->list='n';
                        updateDB(getCurrentMakeID($userID), $DB);
                        setUserStep($userID, '4');
                        makeHTTPRequest('sendMessage', [
                            'text' => "Enter the text declaimed his first 🔀 \n to stop, employee /cancel Enter",
                            'chat_id' => $userID
                        ]);
                    }
                }break;




                case '4':{
                    $text = $update->message->text;
                    if(mb_strlen($text) > 100){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️ too much text! 😊 should be less than 100 characters'.$cancel_Text
                        ]);
                    }elseif(mb_strlen(trim($text)) == 0){
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>'❗️! ️ community! Good one thing 😁 send a blank space or not acceptable'.$cancel_Text
                        ]);
                    }else{
                        var_dump('The DATABASE');
                        var_dump($DB);
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        if($DB->list == 'n') $DB->list = [];
                        $newObject = json_decode('{}');
                        $newObject->text = $text;
                        array_push($DB->list,$newObject);
                        $DB->count++;
                        $index = count(((array) $DB->list));
                        updateDB(getCurrentMakeID($userID),$DB);
                        var_dump('The DATABASE');
                        var_dump($DB);
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ Well done! Text button number {$index} Kurdish records.

Now you have a valid link him write it.

Link could be any link (link period, or even link channel link your site) but must first be careful http:// or https:// Put
Make sure the address is correct.
😜".$cancel_Text
                        ]);
                        setUserStep($userID,'5');
                    }
                }break;


                case '5':{
                    $link = $update->message->text;
                    if(is_valid_url_international($link)){
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $list = (array) $DB->list;
                        echo "The LIST \n";
                        var_dump($list);
                        $lastID = max(array_keys($list));
                        $lastObject = end($list);
                        $lastObject->url = trim($link);
                        $list[$lastID] = $lastObject;
                        $DB->list = $list;
                        $DB->count++;
//                        var_dump($DB);
//                        $index = $DB->count;
//                        $object = end($DB->list);
//                        var_dump($object);
//                        $object->url = $link;
//                        var_dump("new link ... \n");
//                        var_dump($object);
//                        $DB->list[($index-1)] = $object;
                        $index = count($list);
                        updateDB(getCurrentMakeID($userID),$DB);
                        echo "THE DATABASE \n";
                        var_dump($DB);
                        setUserStep($userID,'4');
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"✅ Evil! Link button {$index} was recorded.

Nga now inkjet buttons now complete. If you want to make Mtnsh's new press send.

But if the hotlink button to complete /submit Click 😊".$cancel_Text,
                            'reply_markup'=>[
                                'keyboard'=>[
                                    [
                                        ['text'=>'/submit'],['text'=>'/cancel']
                                    ]
                                ]
                            ]
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage',[
                            'chat_id'=>$userID,
                            'text'=>"! ️ robot diagnosed the wrong link!
I guess I https:// with http:// first killed all 😜".$cancel_Text
                        ]);
                    }
                }break;



                case '6':{
                    $userID = $update->message->from->id;
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>'Congratulations on your list was prepared 👍

Well, I gave you a code, the code inline code that robots called it inline (like me!) They support.

You do not need me to the admin channel.

The code track now (but not write) Wait a list that opens up to you.
Click method when loaded.

If the bad explained /help button is now detailed tips!'
                    ]);

                    $theCommand = '@Paquabot getbanner-'.getCurrentMakeID($update->message->from->id);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$theCommand,
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[
                                [
                                    ['text'=>'Send considered to chat','url'=>'https://telegram.me/share/url?url='.str_replace(' ','%20',$theCommand)]
                                ]
                            ]
                        ])
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'chat_id'=>$update->message->from->id,
                        'text'=>$ad_text."\n<a href='{$ad_url}'>{$ad_btn}</a>",
                        'parse_mode'=>"HTML",
                        'disable_web_page_preview'=>true
                    ]);
                    setUserLocation($update->message->from->id,'home');
                    setUserStep($update->message->from->id,'3');
                }break;



























//                case '4':{
//                    if(is_valid_url_international($userTEXT)) {
//                        $DB = getMakeDB(getCurrentMakeID($userID));
//                        $DB->f->join = $userTEXT;
//                        updateDB(getCurrentMakeID($userID), $DB);
//                        setUserStep($userID, '5');
//                        makeHTTPRequest('sendMessage', [
//                            'text' => "🌐 channel identifier manager \n identifiers such as @iluli are \n \n If the channel does not have a profile command /skipstep click \n to cancel, command /cancel Enter",
//                            'chat_id' => $userID
//                        ]);
//                    }else{
//                        makeHTTPRequest('sendMessage', [
//                            'text' => "Link is not valid. Should http:// or https:// is \n to cancel, command /cancel Enter",
//                            'chat_id' => $userID
//                        ]);
//                    }
//                }break;
//
//                case '5':{
//                    $DB = getMakeDB(getCurrentMakeID($userID));
//                    $DB->f->chid = "@iluli";
//                    $DB->done = true;
//                    updateDB(getCurrentMakeID($userID),$DB);
//                    setUserStep($userID,'1');
//                    setUserLocation($userID,'home');
//                    makeHTTPRequest('sendMessage',[
//                        'text'=>"Your banner ✅ registered successfully \n Copy the code for your banner and whenever you want to use \n \n If you do not know what to do with this code command /help Enter",
//                        'chat_id'=>$userID
//                    ]);
//                    makeHTTPRequest('sendMessage',[
//                        'text'=>'@Paquabot getbanner-'.getCurrentMakeID($userID),
//                        'chat_id'=>$userID
//                    ]);
//                }break;














                case '7':{
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $image_id = isset(end($update->message->photo)->file_id)?end($update->message->photo)->file_id:false;
                    if($image_id === false){
                        makeHTTPRequest('sendMessage',[
                            'text'=>"Wrong Files \n to stop, employee /cancel Enter",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        $imag = makeHTTPRequest('getFile',[
                            'file_id'=>$image_id
                        ]);
                        $t = time().'.jpg';
                        $furl = 'http://ipex.96.it'.$t;
                        $fule = 'https://api.telegram.org/file/bot295804730:AAG_CNVIQWDErlZaok8KmhGsD-bQmiYmWfI/'.trim($imag->result->file_path,"\\/");
                        $file_ext = end(explode(".",$fule));
                        if($file_ext == "jpg" || $file_ext == "jpeg") {
                            file_put_contents("/var/www/html/images/".$t, fopen($fule, 'r'));
                            if(!isset($DB->s)) $DB->s = json_decode("{}");
                            $DB->s->image_id = $furl;
                            updateDB(getCurrentMakeID($userID),$DB);
                            makeHTTPRequest('sendMessage',[
                                'chat_id'=>$userID,
                                'text'=>"Please submit your text \n to stop, employee /cancel Enter"
                            ]);
                            setUserStep($userID,'8');
                        }else{
                            makeHTTPRequest('sendMessage', [
                                'chat_id' => $userID,
                                'text' => "Please send jpg file \n $ file_ext file has been sent to you. \n <a href='http://image.online-convert.com/convert-to-jpg'> click online to become </a> \n to opt-out, command /cancel Enter",
                                'parse_mode'=>"HTML"
                            ]);
                        }
                    }
                }break;

                case '8':{
                    if(mb_strlen($update->message->text) > 300){
                        makeHTTPRequest('sendMessage', [
                            'text' => "Text messages you'.mb_strlen($update->message->text).' Characters. Please send less than 300 characters. \n To opt-out, command /cancel Enter",
                            'chat_id' => $userID
                        ]);
                    }else {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->s->text = $userTEXT;
                        updateDB(getCurrentMakeID($userID), $DB);
                        setUserStep($userID, '9');
                        makeHTTPRequest('sendMessage', [
                            'text' => "Second Jovin link manager \n to stop, employee /cancel Enter",
                            'chat_id' => $userID
                        ]);
                    }
                }break;

                case '9':{

                    if(is_valid_url($userTEXT)) {
                        $DB = getMakeDB(getCurrentMakeID($userID));
                        $DB->s->join = $userTEXT;
                        updateDB(getCurrentMakeID($userID),$DB);
                        setUserStep($userID,'10');
                        makeHTTPRequest('sendMessage',[
                            'text'=>"Channel ID to the manager. Like @WathiqApi \n \n If the channel does not have a profile command /skipstep click \n to cancel, command /cancel Enter",
                            'chat_id'=>$userID
                        ]);
                    }else{
                        makeHTTPRequest('sendMessage', [
                            'text' => "Please do not send a correct link. \n links with https://telegram.me/joinchat begin \n to cancel, command /cancel Enter",
                            'chat_id' => $userID
                        ]);
                    }

                }break;

                case '10':{
                    $DB = getMakeDB(getCurrentMakeID($userID));
                    $DB->s->chid = "@iluli";
                    $DB->done = true;
                    updateDB(getCurrentMakeID($userID),$DB);
                    setUserStep($userID,'1');
                    setUserLocation($userID,'home');
                    makeHTTPRequest('sendMessage',[
                        'text'=>"The second channel was successfully \n channel then enter the following statement on their channels to choose \n \n If you do not know what to do with this code command /help Enter",
                        'chat_id'=>$userID
                    ]);
                    makeHTTPRequest('sendMessage',[
                        'text'=>'@Paquabot getbanner-'.getCurrentMakeID($userID),
                        'chat_id'=>$userID
                    ]);
                }break;

            };
        }break;

        default:{
            $links = [
                'tbd'=>'https://telegram.me/joinchat/DtebJD-YicabzaggOWIHeQ',
                'tbd_c'=>'https://telegram.me/joinchat/DtebJD7THfnZK-RJY1Epow',
                'mhrdev'=>'https://telegram.me/joinchat/DtebJEC6lZdpYFg01oMCdQ',
                'mhrdev_c'=>'https://telegram.me/joinchat/Bzyk9TxB31C0pdke0DMJBg'
            ];
            var_dump(makeHTTPRequest('sendMessage',[
                'chat_id'=>$userID,
                'text'=>"The second channel was successfully \n channel then enter the following statement on their channels to choose \n \n If you do not know what to do with this code command /help Enter",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [['text'=>$ad_btn,'url'=>$ad_url]], 
                        [['text'=>'Notification channel robot','url'=>$links['mhrdev_c']]],
                        [['text'=>'Contact Developer','url'=>'https://telegram.me/iluli']],
                        [['text'=>'Robot channel survey','url'=>'https://telegram.me/Beezinc']]
                    ]
                ])
            ]));
        }

    }

    var_dump([
        'Location'=>$currentLocation,
        'Step'=>$currentStep
    ]);

}

$clean = ob_get_clean();
//file_put_contents('log',$clean);

$userID = isset($update->message)?$update->message->from->id:$update->inline_query->from->id;

if( $userID == $the_admin_id){
    file_put_contents('ola',$clean);
}


