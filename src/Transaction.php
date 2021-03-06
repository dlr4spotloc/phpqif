<?php
namespace StephenHarris\QIF;

class Transaction {

	const CASH = 'Cash';
	const BANK = 'Bank';
	const CCARD = 'CCard';
	const INVST = 'Invst';
	const OTHA = 'Oth A';
	const OTHL = 'Oth L';
	const Invoice = 'Invoice';

	private $type = null;

	private $date = null;

	private $description = null;

	private $amount = null;

	private $category = null;

	private $splits = [];

	private $status = null;

	public function __construct( $type ) {
		$this->type = $type;
	}

	public function setDate( \DateTime $date ) {
		$this->date = $date;
		return $this;
	}

	public function setDescription( $description ) {
		$this->description = $description;
		return $this;
	}

	public function setAmount( $float ) {
		$this->amount = $float;
		return $this;
	}

	public function setCategory( $category ) {
		$this->category = $category;
		return $this;
	}

	public function addSplit( $splitName, $amount, $memo = null ) {
		if ( array_key_exists( $splitName, $this->splits ) ) {
			throw new \Exception( sprintf( 'Split "%s" already exists in this transaction.', $splitName ) );
		}
		$this->splits[$splitName] = [
			'amount' => $amount,
			'memo'   => $memo
		];
		return $this;
	}

	public function removeSplit( $splitName ) {
		unset($this->spits[$splitName]);
		return $this;
	}

	public function markReconciled() {
		$this->status = 'X';
		return $this;
	}

	public function markCleared() {
		$this->status = 'c';
		return $this;
	}

	public function markNotCleared() {
		$this->status = '';
		return $this;
	}

	public function __toString() {

		$output = [
			"!Type:" . $this->type,
			$this->renderDateLineIfNotNull(),
			$this->renderIfNotNull( 'T', $this->amount ),
			$this->renderIfNotNull( 'L', $this->category ),
			$this->renderSplits(),
			$this->renderIfNotNull( 'C', $this->status ),
			$this->renderIfNotNull( 'P', $this->description ),
			'^'
		];

		return implode( PHP_EOL, array_filter( $output ) );
	}

	private function renderDateLineIfNotNull() {
		$output = '';
		if ( $this->date ) {
			$output = 'D' . $this->date->format( 'd/m/Y' );
		}
		return $output;
	}

	private function renderIfNotNull( $characterKey, $value = null ) {
		$output = '';
		if ( !is_null($value) ) {
			$output = "{$characterKey}{$value}";
		}
		return $output;
	}

	private function renderSplits() {

		$output = [];
		if ( $this->splits ) {
			foreach ( $this->splits as $name => $split ) {
				$output[] = 'S' . $name;
				$output[] = '$' . floatval( $split['amount'] );
				$output[] = $this->renderIfNotNull( 'E', $split['memo'] );
			}
		}

		return implode( PHP_EOL, array_filter( $output ) );
	}

}
