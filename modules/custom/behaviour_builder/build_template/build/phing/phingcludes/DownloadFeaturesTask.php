<?php

/**
 * A Phing task to downlaod behat features.
 */
require_once 'phing/Task.php';

/**
 * A Behat task. For downloading features for behaviour testing from behavedrupal.com.
 *
 * @author Unnikrishnan Bhargavakurup <unnikrishnanadoor@gmail.com>
 */
class DownloadFeaturesTask extends Task {

  /**
   * Url from which we need to download the features.
   *
   * @var string
   */
  protected static $url;

  /**
   * session id from which we need to download the features.
   *
   * @var string
   */
  protected static $sid;

  /**
   * Destination to save the features.
   *
   * @var string
   */
  protected static $destination;

  public function main() {
    ob_start();
    echo "\nDownloading features ...";
    ob_flush();
    flush();
    $session_data = json_encode(array('sid' => $this->sid));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $session_data);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, 'zip');
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
      if($download_size > 0) {
        $perc = floor(($downloaded / $download_size) * 100);
        $left = 100 - $perc;
        printf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%%", "", "");
      }
      ob_flush();
      flush();
    });
    curl_setopt($ch, CURLOPT_NOPROGRESS, FALSE); // needed to make progress function work
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0');
    $info = curl_getinfo($ch);
    $data = curl_exec($ch);
    if (curl_errno($ch) !== 0) {
      $info = curl_getinfo($ch);
      print_r($info);
    }
    curl_close($ch);
    ob_flush();
    flush();
    $file = fopen($this->destination, "w+");
    fputs($file, $data);
    fclose($file);
  }

  /**
   * Set the directory to save the features.
   *
   * @param string $suite The suite to use.
   *
   * @return void
   */
  public function setDir($dir) {
    $this->destination = "" . $dir;
  }

  /**
   * Set the url form which we downlaod the feature.
   *
   * @param string $suite The suite to use.
   *
   * @return void
   */
  public function setUrl($url) {
    $this->url = "" . $url;
  }

  /**
   * Set the url form which we downlaod the feature.
   *
   * @param string $suite The suite to use.
   *
   * @return void
   */
  public function setSID($sid) {
    $this->sid = "" . $sid;
  }
}