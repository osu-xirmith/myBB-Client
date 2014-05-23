<?php
class mybbBot {
  private $lastlist, $lastactive, $token, $sid, $b, $u, $p, $h;
  function __construct($url, $user, $pass) {
    $this->b = $url;
    $this->p = $pass;
    $this->u = $user;
    if (!file_get_contents($this->b)) throw new myBBException();
    if (!$this->login($this->u, $this->p)) throw new myBBException();
  }
  private function login($user, $password) {
    global $http_response_header;
    $result = $this->connect($this->b . 'member.php', array(
    'username' => $user, 
    'password' => $password, 
    'action' => "do_login", 
    'url' => ""
      )
      );
    $cookies = array();
    foreach ($http_response_header as $hdr) {
      if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
        parse_str($matches[1], $tmp);
        $cookies += $tmp;
      }
    }
    if (!isset($cookies["mybbuser"])) return false;
    $this->sid = $cookies["sid"];
    $this->token = $cookies["mybbuser"];
    $this->lastvist = $cookies["mybb"]["lastvist"];
    $this->lastactive = $cookies["mybb"]["lastactive"];
    return true;
  }
  private function connect($url, $post) { //Connector function
    global $http_response_header;
    if ($post != null) {
      $opts = array('http' =>
        array(
      'method'  => 'POST', 
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
        "Cookie: mybb[referrer]=1; mybb[lastvisit]=" . $this->lastvist . "; mybb[lastactive]=" . $this->lastactive . "; loginattemps=1; mybbuser=" . $this->token . "; sid=" . $this->sid, 
      'content' => http_build_query($post)
        )
        );
    }
    else {
      $opts = array('http' =>
        array(
      'method'  => 'GET', 
      'header'  => 'Cookie: mybb[referrer]=1; mybb[lastvisit]=' . $this->lastvist . '; mybb[lastactive]=' . $this->lastactive . '; loginattemps=1; mybbuser=' . $this->token . '; sid=' . $this->sid
        )
        );
    }
    $this->h = $http_response_header;
    return file_get_contents($url, false, stream_context_create($opts));
  }
  public function quickReply($url, $msg) { //Post a new reply to a thread at $url
    $html = new DOMDocument();
    if(strpos($url, $this->b) === false) $url = $this->b . $url;
    $data = $this->connect($url, null);
    $data = substr($data, strpos($data, '<form method="post" action="newreply.php'));
    $data = substr($data, 0, strpos($data, '</form>')+7);
    $html->loadHTML($data);
    $els = $html->getelementsbytagname('input');
    $url = $this->b . $html->getElementById('quick_reply_form')->getAttribute('action');
    $list = array();
    foreach($els as $inp) $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
    $list["message"] = $msg;
    unset($list["previewpost"]);
    var_dump($list);
    $this->connect($url, $list);
  }
  public function newThread($id,$t,$c){ //Post a new thread in $id section

  }
  public function rateThread($url){ //Rates thread at $url

  }
  public function urlToID($url){ //For forums with URL rewrites

  }
}
class myBBException extends Exception {}
