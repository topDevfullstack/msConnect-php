<?php

  class MSConnect {

    private $email, $token, $context;
    private static $open_folder_flag, $folder;
    
    public function __construct($email, $token) {
      $this->email = $email;
      $this->token = $token;

      // Create a stream
      $opts = array(
        'http'=>array(
          'method'=>"GET",
          'header'=>"Authorization: Bearer " . $token . "\r\n" .
                    "Content-Type: application/json\r\n"
        )
      );

      $this->context = stream_context_create($opts);
    }

    public function open($folder)
    {
      if($this->open_folder_flag === NULL) {
        $result = file_get_contents('https://graph.microsoft.com/v1.0/me/mailFolders/' . $folder, false, $this->context);
        if($result) {
          $result = true;
          $this->open_folder_flag = true;
          $this->folder = $folder;
        }
        else {
          $result = false;
          $this->open_folder_flag = NULL;
          $this->folder = NULL;
        }
        // var_dump($this->open_folder_flag);
        // var_dump($this->folder);
      } else {
        $result = file_get_contents('https://graph.microsoft.com/v1.0/me/mailFolders/' . $this->folder . '/messages', false, $this->context);
      }
      return $result;
    }

    public function close($folder) {
      $this->folder == $folder ? $this->folder = NULL : $this->folder = $folder;
      $this->folder == $folder ? $this->open_folder_flag = NULL : $this->open_folder_flag = true;
    }

    public function getFolders()
    {
      // Open the file using the HTTP headers set above
      $result = file_get_contents('https://graph.microsoft.com/v1.0/me/mailFolders', false, $this->context);
      return $result;
    }

    public function search($type)
    {
      $result = "";
      if($this->folder !== NULL) {
        $spilt = explode(" ",$type);
        switch($spilt[0]) {
          case "OPEN" : 
            $result = file_get_contents('https://graph.microsoft.com/v1.0/me/mailFolders/' . $this->folder . '/messages?$filter=isRead+ne+true', false, $this->context);
            break;
          case "SINCE" : 
            // $d = strtotime($spilt[1]);
            $result = file_get_contents('https://graph.microsoft.com/v1.0/me/mailFolders/' . $this->folder . '/messages?&filter=receivedDateTime ge 2019-12-15T21:01:15Z', false, $this->context);
            break;
          default : break;
        }
      }
      return $result;
    }

  }
  $ttt = "eyJ0eXAiOiJKV1QiLCJub25jZSI6InVZMEh2NGpRWEdJWlBNLXdRSWJ2U1FRQ3lZR2J5d2VnOVRDQ0dDdFFZS28iLCJhbGciOiJSUzI1NiIsIng1dCI6IjJaUXBKM1VwYmpBWVhZR2FYRUpsOGxWMFRPSSIsImtpZCI6IjJaUXBKM1VwYmpBWVhZR2FYRUpsOGxWMFRPSSJ9.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTAwMDAtYzAwMC0wMDAwMDAwMDAwMDAiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zNDg1YmEyNi0yZjJjLTQ0OGQtYjE3MC05OTRhMTNlYmQ2OGEvIiwiaWF0IjoxNjY3NTk5MDM4LCJuYmYiOjE2Njc1OTkwMzgsImV4cCI6MTY2NzYwMzYzNywiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFUUUF5LzhUQUFBQTBiMjRsWjZzZWlyZnZiZ2RldTVVbkFyNXZ1VzFkeWptYVMzWVJUOExsR1NOMHBmOUFXQnhVK1JsdWd1bE9EUDIiLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6Ik15QXp1cmVBcHAiLCJhcHBpZCI6IjNjZjQ3NmRhLWE3NWQtNDc2Ny1hMDI0LTRhZjZkZjM0OWRkMCIsImFwcGlkYWNyIjoiMCIsImZhbWlseV9uYW1lIjoiQnJvb2tzIiwiZ2l2ZW5fbmFtZSI6IkhheWxleSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjIwLjYyLjI1My44OSIsIm5hbWUiOiJIYXlsZXkgQnJvb2tzIiwib2lkIjoiNmZmMGY5NmYtZTk4Yi00MTUzLWE3NjctNmZjYTQxMzkyMGNlIiwicGxhdGYiOiIzIiwicHVpZCI6IjEwMDM3RkZFQUNDMzUyQzgiLCJwd2RfZXhwIjoiMzE1MzEyNTU2IiwicHdkX3VybCI6Imh0dHBzOi8vcHJvZHVjdGl2aXR5LnNlY3VyZXNlcnZlci5uZXQvbWljcm9zb2Z0P21hcmtldGlkPWVuLVVTXHUwMDI2ZW1haWw9aGF5bGV5LmJyb29rcyU0MGFjaWZtYS5jb21cdTAwMjZzb3VyY2U9Vmlld1VzZXJzXHUwMDI2YWN0aW9uPVJlc2V0UGFzc3dvcmQiLCJyaCI6IjAuQVhvQUpycUZOQ3d2alVTeGNKbEtFLXZXaWdNQUFBQUFBQUFBd0FBQUFBQUFBQUI2QUpzLiIsInNjcCI6IkNhbGVuZGFycy5SZWFkIE1haWwuUmVhZCBNYWlsLlNlbmQgTWFpbGJveFNldHRpbmdzLlJlYWQgb3BlbmlkIHByb2ZpbGUgVXNlci5SZWFkIGVtYWlsIiwic3ViIjoiY21zWkpDOGptUFVRWkloelN2MHdzelhJa1djWGJJMkdCTkQydGcwU1Y4cyIsInRlbmFudF9yZWdpb25fc2NvcGUiOiJFVSIsInRpZCI6IjM0ODViYTI2LTJmMmMtNDQ4ZC1iMTcwLTk5NGExM2ViZDY4YSIsInVuaXF1ZV9uYW1lIjoiaGF5bGV5LmJyb29rc0BhY2lmbWEuY29tIiwidXBuIjoiaGF5bGV5LmJyb29rc0BhY2lmbWEuY29tIiwidXRpIjoiZExxS0YxWGljay1xQU5jY2JSUW1BQSIsInZlciI6IjEuMCIsIndpZHMiOlsiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19zdCI6eyJzdWIiOiJ5MmF2NFJBeS1LVXdJWDdnQlp6R2pXRTFBWWJRT2Z2bWpMRUl2dnE1OFVnIn0sInhtc190Y2R0IjoxNDExMzg1MDM2LCJ4bXNfdGRiciI6IkVVIn0.c7Wi5S8PXHMGOr6_0B5ixzkZAa_5I8dcPZIwhyZHFTQNuECjJBEORXHxQinKsogTcoaNjepalYFAt8PfwMXSd3BlmLgRueZUF6qt2cuS_tfTzLjrFYnwhNfOR6l2Y75FQlk-9MCuOk6DAfsSXEcc2h4GDY0PSAEDPNiCR4cjWQndj4YAE231ANM-wdJ5n7RCQcGKDgzlnFzZ9bQWIijss_QTr41KWxGf3tWDL1xKg13I6J_AKHSVoJsy8aRYRZcWalIORSmmdm_pML8encU916gtiX_Wgn99t-jeJiaG9h4Rbgpr6lCuCZCTkbXWbZBM0MXq5H_j-mN-3d6Bplj0KA";
  $conn = new MSConnect("a.bodha@arithon.com", $ttt);
  echo $conn->getFolders();
  echo "<p><p><p>";

  $id = "AQMkADJhMTUwYmE2LTQxODEtNDZlYy1hZjJkLTRkYjA1MTU0OTI3NgAuAAADc9YEOmsu30mdty6VidLYxQEANpAIGsh1O0meb7GFaY_pAQAAAgFfAAAA";
  
  $conn->open($id);
  // echo "<p><p><p>";
  $conn->open($id);
  // echo "<p><p><p>";

  // echo $conn->search("OPEN");
  // echo "<p><p><p>";
  echo $conn->search("SINCE 2019-12-15");
?>