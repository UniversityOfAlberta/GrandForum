<?php

/// An empty class providing helper methods for dealing with arrays.
class ArrayUtil {
	// Tests whether a field #f is set in #arr, and returns it if set,
	// #def otherwise.
	static function get_field(&$arr, $f, $def = false) {
		if (is_array($arr) && array_key_exists($f, $arr)) {
			return $arr[$f];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f is set in #arr, and returns it if set,
	// #def otherwise.
	static function get_array(&$arr, $f, $def = array()) {
		if (is_array($arr) && array_key_exists($f, $arr)) {
			return (array)$arr[$f];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f1 and its subfield #f2 are set in #arr,
	// returning the inner array if set, #def otherwise.
	static function get_subarray(&$arr, $f1, $f2, $def = array()) {
		if (is_array($arr) && array_key_exists($f1, $arr) && array_key_exists($f2, $arr[$f1])) {
			return (array)$arr[$f1][$f2];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f1 and its subfield #f2 are set in #arr,
	// returning the inner field if set, #def otherwise.
	static function get_subfield(&$arr, $f1, $f2, $def = false) {
		if (is_array($arr) && array_key_exists($f1, $arr) && array_key_exists($f2, $arr[$f1])) {
			return $arr[$f1][$f2];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f is set in #arr, and returns it if set,
	// empty string otherwise.
	static function get_string(&$arr, $f, $def = "") {
		if (is_array($arr) && array_key_exists($f, $arr) && (strlen($arr[$f]) > 0)) {
			return $arr[$f];
		}
		else {
			return $def;
		}
	}
}
