<?php

namespace FileSystem;

use Exception;
use AttributeHelper\Accessor;

	class File extends FileSystem {

		protected $_path;

		protected function __construct($path) {

			parent::__construct($path);
			$this->_cwd = Dir::path($this->_cwd);

			$this->_path = $this->_cwd->cwd."/".$this->_basename;

			$this->prependUnderscore();
			$this->readonly("path", ["length", "size"], ["parent", function() {
				return clone $this->cwd;
			}]);

			$this->methodsAsProperties("extension", "lines", "name", "read", "size", "stat", "touch");

		}

		function chmod($mode) {
			return chmod($this->_path, $mode);
		}

		function delete() {
			return unlink($this->_path);
		}

		function extension() {
			return pathinfo($this->_path)["extension"];
		}

		function isFile() {
			return true;
		}

		function lines($flags = 0) {
			return file($this->_path, $flags);
		}

		function name() {
			return pathinfo($this->_path)["filename"];
		}

		static function open($filename) {
			return new File($filename);
		}

		function read($length = -1) {

			if (!realpath($this->_path)) {
				throw new Exception("Could not find path '".$this->_path."'. No such file or directory.", 1);
			}

			$file = fopen($this->_path, "r");

			if ($length == -1) {
				$length = filesize($this->_path);
			}

			$content = fread($file, $length);
			fclose($file);

			return $content;

		}

		function rename($newName) {

			if ($newName[0] != "/") {
				$newName = $this->_cwd->cwd."/".$newName;
			}

			if (@rename($this->_path, $newName)) {

				$this->_path = realpath($newName);
				$this->setBasename($newName);
				$this->_cwd = new Dir($this->_path);

				return true;

			}

			return false;

		}

		function size() {
			return filesize($this->_path);
		}

		function stat() {
			return stat($this->_path);
		}

		function __toString() {
			return $this->_path;
		}

		function touch() {

			if (!@touch($this->_path)) {
				return false;
			}

		}

		function write($content, $append = "w") {

			if (!is_string($content)) {
				throw new Exception("FileSystem\\File::write() expects content as string.", 1);
			}

			$file = fopen($this->_path, $append);
			fwrite($file, $content);
			fclose($file);

			return $this;

		}

	}

?>