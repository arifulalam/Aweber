<?php
    require('aweber_api/aweber_api.php');

    $app = new AWeber();
    
    echo "<pre>";
    print_r($app->moveSubscriber('ariful-alam@hotmail.com', 'DESTINATION_LIST_NAME'));
    echo "</pre>";

    class AWeber{
        function __construct() {
            $this->consumerKey      = 'CONSUMER_KEY';
            $this->consumerSecret   = 'CONSUMER_SECRET';
            $this->accessKey        = 'ACCESS_KEY';
            $this->accessSecret     = 'ACCESS_SECRET';

            $this->application      = new AWeberAPI($this->consumerKey, $this->consumerSecret);
            $this->account          = $this->application->getAccount($this->accessKey, $this->accessSecret);
        }

        function getList($listUniqueID) {
            if(!empty($listUniqueID)){
                try {
                    $list = $this->account->lists->find(array('name' => $listUniqueID));
                    return $list;
                }catch(AWeberAPIException $AWexc) {
                    $aweberExc = "<h3>AWeberAPIException:</h3>";
                    $aweberExc .= " <li> Type: $AWexc->type              <br>";
                    $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                    $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                    $aweberExc .= "<hr>";
                    return $aweberExc;
                }catch(Exception $exc) {
                    return $exc;
                }
            }
            return "No unique list id supplied.";
        }

        function getLists(){
            return $this->account->lists->data['entries'];
        }

        function listSubscribers($listUniqueID){
            if(!empty($listUniqueID)){
                $subscribers = array();
                foreach ($this->account->lists as $list) {
                    if($list->unique_list_id === $listUniqueID){
                        foreach ($list->subscribers as $subscriber) {
                            array_push($subscribers, array('name' => $subscriber->name, 'email' => $subscriber->email));
                        }
                    }
                }
                return $subscribers;
            }
            return "No unique list id supplied.";
        }

        function getListSubscribers($listUniqueID){
            if(!empty($listUniqueID)){
                $list = $this->getList($listUniqueID);
                //print_r($list[0]->total_subscribed_subscribers);

                $listUrl = "{$list[0]->url}/subscribers";
                $subscribers_collection = $this->account->loadFromUrl($listUrl);

                $subscribers = array();
                foreach ($subscribers_collection as $subscriber) {
                    array_push($subscribers, array('name' => $subscriber->name, 'email' => $subscriber->email));
                }
                return $subscribers;
            }
            return "No unique list id supplied.";
        }

        function getSubscriber($email) {
            if(!empty($email)){
                try {
                    $subscriber = $this->account->findSubscribers(array('email' => $email));
                    return $subscriber->data;
                }catch(AWeberAPIException $AWexc) {
                    $aweberExc = "<h3>AWeberAPIException:</h3>";
                    $aweberExc .= " <li> Type: $AWexc->type              <br>";
                    $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                    $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                    $aweberExc .= "<hr>";
                    return $aweberExc;
                }catch(Exception $exc){
                    return $exc;
                }
            }
            return "No email id supplied to check.";
        }

        function addSubscriber($subscriber, $list) {
            try {
                $listUrl = "/accounts/{$this->account->id}/lists/{$list->id}";
                $list = $this->account->loadFromUrl($listUrl);

                $newSubscriber = $list->subscribers->create($subscriber);
            }catch(AWeberAPIException $AWexc) {
                $aweberExc = "<h3>AWeberAPIException:</h3>";
                $aweberExc .= " <li> Type: $AWexc->type              <br>";
                $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                $aweberExc .= "<hr>";
                return $aweberExc;
            }catch(Exception $exc) {
                return $exc;
            }
            return $newSubscriber;
        }

        function updateSubscriber($email, $updates = array()){
            if(!empty($email)){
                try{
                    $subscriber = $this->account->findSubscribers(array('email' => $email));
                    $subscriber = $subscriber[0];
                    if(!empty($subscriber)){
                        foreach ($updates as $key => $value) {
                            $subscriber->$key = $value;
                        }
                        $update = $subscriber->save();
                        return $update;
                    }
                    return "No subscriber found.";
                }catch(AWeberAPIException $AWexc) {
                    $aweberExc = "<h3>AWeberAPIException:</h3>";
                    $aweberExc .= " <li> Type: $AWexc->type              <br>";
                    $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                    $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                    $aweberExc .= "<hr>";
                    return $aweberExc;
                }catch(Exception $exc){
                    return $exc;
                }
            }
            return "No email address supplied.";
        }

        function moveSubscriber($email, $new_listUniqueID){
            try{
                $subscriber = $this->account->findSubscribers(array('email' => $email));

                $list = $this->account->lists->find(array('name' => $new_listUniqueID));//$this->getList($new_listUniqueID);
                $dest_list = $list[0];
                $move = $subscriber->move($dest_list);
                //print_r($list[0]);
                //echo "<br/>" . $move;
            }catch(AWeberAPIException $AWexc) {
                $aweberExc = "<h3>AWeberAPIException:</h3>";
                $aweberExc .= " <li> Type: $AWexc->type              <br>";
                $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                $aweberExc .= "<hr>";
                return $aweberExc;
            }catch(Exception $exc){
                return $exc;
            }
        }

        function unsubscribe($email){
            if(!empty($email)){
                try{
                    $subscriber = $this->account->findSubscribers(array('email' => $email));
                    $subscriber = $subscriber[0];
                    if(!empty($subscriber)){
                        $subscriber->status = 'unsubscribed';
                        $save = $subscriber->save();
                        return $save;
                    }
                    return "No subscriber found.";
                }catch(AWeberAPIException $AWexc) {
                    $aweberExc = "<h3>AWeberAPIException:</h3>";
                    $aweberExc .= " <li> Type: $AWexc->type              <br>";
                    $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                    $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                    $aweberExc .= "<hr>";
                    return $aweberExc;
                }catch(Exception $exc){
                    return $exc;
                }
            }
            return "No email address supplied.";
        }

        function deleteSubscriber($email){
            if(!empty($email)){
                //$subscriber = $this->getSubscriber($email);
                try{
                    $subscriber = $this->account->findSubscribers(array('email' => $email));
                    $subscriber = $subscriber[0];
                    if(!empty($subscriber)){
                        $delete = $subscriber->delete();
                        return $delete;
                    }
                    return "No subscriber found.";
                }catch(AWeberAPIException $AWexc) {
                    $aweberExc = "<h3>AWeberAPIException:</h3>";
                    $aweberExc .= " <li> Type: $AWexc->type              <br>";
                    $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                    $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                    $aweberExc .= "<hr>";
                    return $aweberExc;
                }catch(Exception $exc){
                    return $exc;
                }
            }
            return "No email address supplied.";
        }
    }
?>
