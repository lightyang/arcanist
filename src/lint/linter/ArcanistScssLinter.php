<?php

/**
 * Uses `scss-lint` to detect various errors in Sass code.
 */
final class ArcanistScssLinter extends ArcanistExternalLinter {

  public function getInfoURI() {
    return 'https://github.com/causes/scss-lint';
  }

  public function getInfoName() {
    return pht('scss-lint');
  }

  public function getInfoDescription() {
    return pht('Use `scss-lint` to check for syntax errors in Sass source files.');
  }

  public function getLinterName() {
    return 'SCSS-LINT';
  }

  public function getLinterConfigurationName() {
    return 'scss-lint';
  }

  public function getDefaultBinary() {
    return 'scss-lint';
  }

  public function getVersion() {
    list($stdout) = execx('%C --version', $this->getExecutableCommand());
    return $stdout[0];
  }

  public function getInstallInstructions() {
    return pht('Install `scss-lint` using `gem install scss-lint`.');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $lines = phutil_split_lines($stdout, false);

    $messages = array();
    foreach ($lines as $line) {
      $matches = null;
      if (!preg_match('/^(.*?):(\d+) \[(\w)\] (\w+): (.*)$/', $line, $matches)) {
        continue;
      }
      foreach ($matches as $key => $match) {
        $matches[$key] = trim($match);
      }
      $message = new ArcanistLintMessage();
      $message->setPath($path);
      $message->setLine($matches[2]);
      $message->setCode($matches[3]);
      $message->setName('scss-lint '.$matches[4]);
      $message->setDescription($matches[5]);
      $message->setSeverity($this->getLintMessageSeverity($matches[3]));

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
      return ArcanistLintSeverity::SEVERITY_ERROR;
      break;
    default:
      return ArcanistLintSeverity::SEVERITY_WARNING;
      break;
    }
  }

}
