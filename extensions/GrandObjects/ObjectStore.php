<?php

/// Provides a generic object store, for caching frequently-used objects.
///
/// The key idea is to transparently cache invariant objects that tend to be
/// instantiated multiple times (e.g.: Person objects).
///
/// To use, one must:
/// * Instantiate an ObjectStore;
/// * Make sure that new instances of the invariants are included in the store
///   (by invoking the method set() with the proper parameters).
/// * Before instantiating, querying the store with the get() method.
class ObjectStore {
	var $store;

	// Some statistics.
	var $hits;
	var $misses;
	var $n_sets;
	var $n_repl;

	function __construct() {
		$this->store = array();
		$this->hits = 0;
		$this->misses = 0;
		$this->n_repl = 0;
	}

	/// Retrieve an invariant from the store, possibly included at #index.
	/// If not found, returns false.
	public function get($index) {
		if (array_key_exists($index, $this->store)) {
			$hits++;
			return $this->store[$index];
		}
		else {
			$misses++;
			return false;
		}
	}

	/// Stores an invariant #ref (a reference) at index #index in the store.
	public function set(&$ref, $index) {
		if (! is_scalar($index))
			throw new DomainException("Index used in ObjectStore is not scalar.");

		if (array_key_exists($index, $this->store))
			$this->n_repl++;
		else
			$this->n_sets++;

		$this->store[$index] = $ref;
	}

	/// Returns an array of statistics for this object store.
	public function stats() {
		return array('hits' => $this->hits, 'misses' => $this->misses,
				'sets' => $this->n_sets, 'replaces' => $this->n_repl);
	}
}
