<?php

namespace FileSystem;

use Exception;
use ArrayUtils\Arrays;

	class Dir extends FileSystem {

		protected function __construct($path) {

			parent::__construct($path);

			$this->methodsAsProperties("children");

		}

		function isDir() {
			return true;
		}

		function chdir($dir) {

			if (strpos($dir, "/") !== 0) {
				$dir = $this->_cwd."/".$dir;
			}

			if (($path = realpath($dir)) === false) {
				throw new Exception("Could not find '$dir'. No such file or directory.", 1);
			}

			$this->setBasename($path);
			$this->setCwd($path);

			return $this;

		}

		function children($subPath = false) {

			$children = new Arrays(glob($this->_cwd.(!empty($subPath) ? "/".trim($subPath, "/") : "")."/*"));

			return $children->map(function($e) {
				return static::path($e);
			});

		}

		function chmod($mode) {
			return chmod($this->_cwd, $mode);
		}

		function has($path) {

			if (strpos($path, "/") !== 0) {
				$path = $this->_cwd."/".trim($path, "/");
			}

			return realpath($path);

		}

		function mkdir($dir, $permissions = 0755, $recursive = true) {

			if (strpos($dir, "/") !== 0) {
				$dir = $this->_cwd."/".$dir;
			}

			if (file_exists($dir)) {
				return 1;
			}

			return mkdir($dir, $permissions, $recursive);

		}

		function rename($newName) {

			if ($newName[0] != "/") {
				$newName = static::parent($this->_cwd, true)."/".$newName;
			}

			if (@rename($this->_cwd, $newName)) {

				$this->_cwd = realpath($newName);
				$this->setBasename($newName);

				return true;

			}

			return false;

		}

		function __toString() {
			return $this->_cwd;
		}

		function touch($filename) {

			$filename = $this->_cwd."/".$filename;
			if (!@touch($filename)) {
				return false;
			}

			return File::open($filename);

		}

	}

?>