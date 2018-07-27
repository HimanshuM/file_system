<?php

namespace FileSystem;

use Exception;
use ArrayUtils\Arrays;
use AttributeHelper\Accessor;

	abstract class FileSystem {

		use Accessor;

		protected $_cwd;
		protected $_basename;

		protected function __construct($path) {

			if (is_dir($path)) {

				$this->setBasename($path);
				$this->setCwd($path);

			}
			else {

				$this->setBasename($path);
				$this->setCwd(self::parent($path, true));

			}

			$this->prependUnderscore();
			$this->readonly("cwd", "basename");
			$this->methodsAsProperties("isDir", "isFile");

		}

		static function basename($path) {

			if (empty($path)) {
				return null;
			}

			$path = str_replace("\\", "/", $path);
			$components = new Arrays(explode("/", $path));

			return $components->last;

		}

		abstract function chmod($mode);

		static function cwd() {
			return static::path(getcwd());
		}

		static function exists($path) {
			return realpath($path);
		}

		function isDir() {
			return false;
		}

		function isFile() {
			return false;
		}

		static function open($path) {
			return static::path($path);
		}

		static function parent($path, $name = false) {

			$path = str_replace("\\", "/", $path);
			$components = explode("/", $path);

			if ($name) {
				return realpath(implode("/", array_slice($components, 0, -1)));
			}
			else {
				return new Dir(realpath(implode("/", array_slice($components, 0, -1))));
			}

		}

		static function path($path) {

			if (($realPath = realpath($path)) === false) {
				throw new Exception("Could not find path '$path'. No such file or directory.", 1);
			}

			if (is_dir($realPath)) {
				return new Dir($realPath);
			}
			else {
				return new File($realPath);
			}

		}

		protected function setCwd($dir) {
			$this->_cwd = $dir;
		}

		protected function setBaseName($dir) {
			$this->_basename = self::basename($dir);
		}

		abstract function __toString();

	}

?>