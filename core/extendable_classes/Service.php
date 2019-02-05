<?php

abstract class Service extends Base implements IService {
	abstract public function initialize_after_injection();
}