<?php

/**
 * Uses `rubocop` to detect various errors in Ruby code.
 */
final class ArcanistRubocopLinter extends ArcanistExternalLinter {

  public function getInfoURI() {
    return 'https://github.com/bbatsov/rubocop';
  }

  public function getInfoName() {
    return pht('Rubocop');
  }

  public function getInfoDescription() {
    return pht('Use `rubocop` to check for syntax errors in Ruby source files.');
  }

  public function getLinterName() {
    return 'RUBOCOP';
  }

  public function getLinterConfigurationName() {
    return 'rubocop';
  }

  public function getDefaultBinary() {
    return 'rubocop';
  }

  public function getVersion() {
    list($stdout) = execx('%C --version', $this->getExecutableCommand());
    return $stdout[0];
  }

  public function getInstallInstructions() {
    return pht('Install `rubocop` using `gem install rubocop`.');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $lines = phutil_split_lines($stdout, false);

    $messages = array();
    foreach ($lines as $line) {
      $matches = null;
      if (!preg_match('/^(.*?):(\d+):(\d+): (\w): (.*)$/', $line, $matches)) {
        continue;
      }
      foreach ($matches as $key => $match) {
        $matches[$key] = trim($match);
      }
      $message = new ArcanistLintMessage();
      $message->setPath($path);
      $message->setLine($matches[2]);
      $message->setChar($matches[3]);
      $message->setCode($matches[4]);
      $message->setName('Rubocop '.$matches[4]);
      $message->setDescription($matches[5]);
      $message->setSeverity($this->getLintMessageSeverity($matches[4]));

      $messages[] = $message;
    }

    if ($err && !$messages) {
      return false;
    }

    return $messages;
  }

  protected function getDefaultMessageSeverity($code) {
    switch ($code) {
    case "E":
    case "F":
      return ArcanistLintSeverity::SEVERITY_ERROR;
      break;
    case "R":
      return ArcanistLintSeverity::SEVERITY_ADVICE;
      break;
    default:
      return ArcanistLintSeverity::SEVERITY_WARNING;
      break;
    }
  }

}
