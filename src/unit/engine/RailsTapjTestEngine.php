<?php

final class RailsTapjTestEngine extends ArcanistUnitTestEngine {

  public function run() {
    $output = new TempFile();
    $command = 'rpt=tapj bundle exec rake test | tee ' . $output . ' | bundle exec tapout pro';
    $future = new ExecFuture($command);

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;

      sleep(0.5);
    } while (!$future->isReady());

    return $this->parseOutput(Filesystem::readFile($output));
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output) {
    $results = array();
    $lines = explode(PHP_EOL, trim($output));

    $case = "";
    foreach ($lines as $index => $line) {
      $json = json_decode($line, true);
      switch ($json['type']) {
      case 'case':
        $case = $json['label'];
        break;

      case 'test':
        $result = new ArcanistUnitTestResult();
        $result->setName($case . "#" . $json['label']);
        $result->setDuration($json['time']);

        switch ($json['status']) {
        case 'pass':
          $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
          break;
        case 'omit':
          $result->setResult(ArcanistUnitTestResult::RESULT_SKIP);
          break;
        case 'fail':
          $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
          $result->setUserData($json['exception']['message']);
          break;
        }

        $results[] = $result;
        break;

      case '':
        $result = new ArcanistUnitTestResult();
        $result->setName("Unknown Error: " . substr($line, 0, 60));
        $result->setResult(ArcanistUnitTestResult::RESULT_BROKEN);
        $result->setUserData($line);
        $results[] = $result;
        break;
      }
    }

    return $results;
  }
}

