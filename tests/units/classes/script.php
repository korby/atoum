<?php

namespace mageekguy\atoum\tests\units;

use
	mageekguy\atoum,
	mageekguy\atoum\script\prompt,
	mock\mageekguy\atoum as mock
;

require_once __DIR__ . '/../runner.php';

class script extends atoum\test
{
	public function testClassConstants()
	{
		$this->string(atoum\script::padding)->isEqualTo('   ');
	}

	public function test__construct()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->string($script->getName())->isEqualTo($name)
				->object($script->getAdapter())->isInstanceOf('mageekguy\atoum\adapter')
				->object($script->getLocale())->isInstanceOf('mageekguy\atoum\locale')
				->object($script->getArgumentsParser())->isInstanceOf('mageekguy\atoum\script\arguments\parser')
				->object($script->getOutputWriter())->isInstanceOf('mageekguy\atoum\writers\std\out')
				->object($script->getErrorWriter())->isInstanceOf('mageekguy\atoum\writers\std\err')
				->array($script->getHelp())->isEmpty()
				->object($script->getCli())->isEqualTo(new atoum\cli())
				->integer($script->getVerbosityLevel())->isZero()
			->and($script = new mock\script($name = uniqid(), $adapter = new atoum\adapter()))
			->then
				->string($script->getName())->isEqualTo($name)
				->object($script->getAdapter())->isIdenticalTo($adapter)
				->object($script->getLocale())->isInstanceOf('mageekguy\atoum\locale')
				->object($script->getArgumentsParser())->isInstanceOf('mageekguy\atoum\script\arguments\parser')
				->object($script->getOutputWriter())->isInstanceOf('mageekguy\atoum\writers\std\out')
				->object($script->getErrorWriter())->isInstanceOf('mageekguy\atoum\writers\std\err')
				->array($script->getHelp())->isEmpty()
				->object($script->getCli())->isEqualTo(new atoum\cli())
				->integer($script->getVerbosityLevel())->isZero()
			->if($adapter = new atoum\test\adapter())
			->and($adapter->php_sapi_name = uniqid())
			->then
				->exception(function() use ($adapter, & $name) {
						new mock\script($name = uniqid(), $adapter);
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\logic')
					->hasMessage('\'' . $name . '\' must be used in CLI only')
		;
	}

	public function testSetAdapter()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->object($script->setAdapter($adapter = new atoum\adapter()))->isIdenticalTo($script)
				->object($script->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testSetLocale()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->object($script->setLocale($locale = new atoum\locale()))->isIdenticalTo($script)
				->object($script->getLocale())->isIdenticalTo($locale)
		;
	}

	public function testSetCli()
	{
		$this
			->if($script = new mock\script(uniqid()))
			->then
				->object($script->setCli($cli = new atoum\cli()))->isIdenticalTo($script)
				->object($script->getCli())->isIdenticalTo($cli)
				->object($script->setCli())->isIdenticalTo($script)
				->object($script->getCli())
					->isNotIdenticalTo($cli)
					->isEqualTo(new atoum\cli())
		;
	}

	public function testSetArgumentParser()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->object($script->setArgumentsParser($argumentsParser = new atoum\script\arguments\parser()))->isIdenticalTo($script)
				->object($script->getArgumentsParser())->isIdenticalTo($argumentsParser)
		;
	}

	public function testSetOutputWriter()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->object($script->setOutputWriter($outputWriter = new atoum\writers\std\out()))->isIdenticalTo($script)
				->object($script->getOutputWriter())->isIdenticalTo($outputWriter)
				->object($script->setOutputWriter())->isIdenticalTo($script)
				->object($script->getOutputWriter())->isEqualTo(new atoum\writers\std\out($script->getCli()))
		;
	}

	public function testSetErrorWriter()
	{
		$this
			->if($script = new mock\script($name = uniqid()))
			->then
				->object($script->setErrorWriter($outputWriter = new atoum\writers\std\out()))->isIdenticalTo($script)
				->object($script->getErrorWriter())->isIdenticalTo($outputWriter)
				->object($script->setErrorWriter())->isIdenticalTo($script)
				->object($script->getErrorWriter())->isEqualTo(new atoum\writers\std\err($script->getCli()))
		;
	}

	public function testSetPrompt()
	{
		$this
			->if($script = new mock\script(uniqid()))
			->then
				->object($script->setPrompt($prompt = new prompt()))->isIdenticalTo($script)
				->object($script->getPrompt())->isIdenticalTo($prompt)
				->object($prompt->getOutputWriter())->isIdenticalTo($script->getOutputWriter())
				->object($script->setPrompt())->isIdenticalTo($script)
				->object($script->getPrompt())
					->isNotIdenticalTo($prompt)
					->isEqualTo(new prompt())
				->object($script->getPrompt()->getOutputWriter())->isIdenticalTo($script->getOutputWriter())
		;
	}

	public function testAddArgumentHandler()
	{
		$this
			->if($argumentsParser = new mock\script\arguments\parser())
			->and($argumentsParser->getMockController()->addHandler = function() {})
			->and($script = new mock\script($name = uniqid()))
			->and($script->setArgumentsParser($argumentsParser))
			->then
				->object($script->addArgumentHandler($handlerA = function() {}, $argumentsA = array('-a')))->isIdenticalTo($script)
				->mock($argumentsParser)->call('addHandler')->withArguments($handlerA, $argumentsA)->once()
				->array($script->getHelp())->isEmpty()
				->object($script->addArgumentHandler($handlerB = function() {}, $argumentsB = array('-b', '--b'), $valuesB = '<argumentB>'))->isIdenticalTo($script)
				->mock($argumentsParser)->call('addHandler')->withArguments($handlerB, $argumentsB)->once()
				->array($script->getHelp())->isEmpty()
				->object($script->addArgumentHandler($handlerC = function() {}, $argumentsC = array('-c', '--c'), $valuesC = '<argumentC>', $helpC = 'help of C argument'))->isIdenticalTo($script)
				->mock($argumentsParser)->call('addHandler')->withArguments($handlerC, $argumentsC)->once()
				->array($script->getHelp())->isEqualTo(array(array($argumentsC, $valuesC, $helpC)))
		;
	}

	public function testSetDefaultArgumentHandler()
	{
		$this
			->if($argumentsParser = new mock\script\arguments\parser())
			->and($argumentsParser->getMockController()->addHandler = function() {})
			->and($script = new mock\script($name = uniqid()))
			->and($script->setArgumentsParser($argumentsParser))
			->then
				->object($script->setDefaultArgumentHandler($defaultHandler = function($script, $argument) {}))->isIdenticalTo($script)
				->mock($argumentsParser)->call('setDefaultHandler')->withArguments($defaultHandler)->once()
				->array($script->getHelp())->isEmpty()
		;
	}

	public function testIncreaseVerbosityLevel()
	{
		$this
			->if($script = new mock\script(uniqid()))
			->then
				->object($script->increaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isEqualTo(1)
				->object($script->increaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isEqualTo(2)
				->object($script->increaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isEqualTo(3)
		;
	}

	public function testDecreaseVerbosityLevel()
	{
		$this
			->if($script = new mock\script(uniqid()))
			->then
				->object($script->DecreaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isZero()
			->if($script->increaseVerbosityLevel())
			->then
				->object($script->DecreaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isZero()
				->object($script->DecreaseVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isZero()
		;
	}

	public function testResetVerbosityLevel()
	{
		$this
			->if($script = new mock\script(uniqid()))
			->then
				->object($script->resetVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isZero()
			->if($script->increaseVerbosityLevel())
			->and($script->increaseVerbosityLevel())
			->then
				->object($script->resetVerbosityLevel())->isIdenticalTo($script)
				->integer($script->getVerbosityLevel())->isZero()
		;
	}

	public function testHelp()
	{
		$this
			->if($argumentsParser = new mock\script\arguments\parser())
			->and($argumentsParser->getMockController()->addHandler = function() {})
			->and($locale = new mock\locale())
			->and($locale->getMockController()->_ = function($string) { return $string; })
			->and($script = new mock\script($name = uniqid()))
			->and($script->setArgumentsParser($argumentsParser))
			->and($script->setLocale($locale))
			->and($script->getMockController()->writeMessage = $script)
			->and($script->getMockController()->writeLabels = $script)
			->then
				->object($script->help())->isIdenticalTo($script)
				->mock($script)->call('writeMessage')->never()
				->mock($script)->call('writeLabels')->never()
			->if($script->addArgumentHandler(function() {}, array('-c', '--c'), $valuesC = '<argumentC>', $helpC = 'help of C argument'))
			->then
				->object($script->help())->isIdenticalTo($script)
				->mock($locale)->call('_')->withArguments('Usage: %s [options]')->once()
				->mock($script)->call('writeMessage')->withArguments('Usage: ' . $script->getName() . ' [options]' . PHP_EOL)->once()
				->mock($script)->call('writeLabels')->withArguments(array('-c <argumentC>, --c <argumentC>' => $helpC), 1)->once()
		;
	}

	public function testRun()
	{
		$this
			->if($script = new mock\script(uniqid(), $adapter = new atoum\test\adapter()))
			->and($argumentsParser = new mock\script\arguments\parser())
			->and($argumentsParser->getMockController()->addHandler = function() {})
			->and($script->setArgumentsParser($argumentsParser))
			->then
				->object($script->run())->isIdenticalTo($script)
				->mock($argumentsParser)->call('parse')->withArguments($script, array())->once()
				->adapter($adapter)->call('ini_set')->withArguments('log_errors_max_len', 0)->once()
				->adapter($adapter)->call('ini_set')->withArguments('log_errors', 'Off')->once()
				->adapter($adapter)->call('ini_set')->withArguments('display_errors', 'stderr')->once()
		;
	}

	public function testPrompt()
	{
		$this
			->if($prompt = new mock\script\prompt())
			->and($this->calling($prompt)->ask = $answer = uniqid())
			->and($script = new mock\script(uniqid()))
			->and($script->setPrompt($prompt))
			->then
				->string($script->prompt($message = uniqid()))->isEqualTo($answer)
				->mock($prompt)->call('ask')->withIdenticalArguments($message)->once()
				->string($script->prompt(($message = ' ' . $message) . "\t\n"))->isEqualTo($answer)
				->mock($prompt)->call('ask')->withIdenticalArguments($message)->once()
			->if($this->calling($prompt)->ask = ' ' . ($answer = uniqid()) . "\t")
			->then
				->string($script->prompt($message = uniqid()))->isEqualTo($answer)
				->mock($prompt)->call('ask')->withIdenticalArguments($message)->once()
		;
	}

	public function testWriteMessage()
	{
		$this
			->if($stdOut = new mock\writers\std\out())
			->and($stdOut->getMockCOntroller()->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setOutputWriter($stdOut))
			->then
				->object($script->writeMessage($message = uniqid()))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->once()
				->object($script->writeMessage(($message = uniqid()) . PHP_EOL))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->once()
				->object($script->writeMessage(($message = uniqid()) . ' ' . PHP_EOL))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->once()
				->object($script->writeMessage(($message = PHP_EOL . $message) . ' ' . PHP_EOL))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->once()
				->object($script->writeMessage($message = uniqid(), false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->once()
				->object($script->writeMessage(($message = uniqid()) . PHP_EOL, false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->once()
				->object($script->writeMessage(($message = uniqid()) . ' ' . PHP_EOL, false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->once()
				->object($script->writeMessage(($message = PHP_EOL . $message) . ' ' . PHP_EOL, false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->once()
		;
	}

	public function testWriteError()
	{
		$this
			->if($locale = new mock\locale())
			->and($stderr = new mock\writers\std\err())
			->and($this->calling($stderr)->clear = $stderr)
			->and($this->calling($stderr)->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setErrorWriter($stderr))
			->and($script->setLocale($locale))
			->then
				->object($script->writeError($message = uniqid()))->isIdenticalTo($script)
				->mock($stderr)
					->call('clear')->once()
					->call('write')->withIdenticalArguments('Error: ' . $message . PHP_EOL)->once()
				->mock($locale)->call('_')->withArguments('Error: %s')->once()
				->object($script->writeError(($message = uniqid()) . PHP_EOL))->isIdenticalTo($script)
				->mock($stderr)
					->call('clear')->twice()
					->call('write')->withIdenticalArguments('Error: ' . $message . PHP_EOL)->once()
				->mock($locale)->call('_')->withArguments('Error: %s')->exactly(2)
				->object($script->writeError(($message = uniqid()) . ' ' . PHP_EOL))->isIdenticalTo($script)
				->mock($stderr)
					->call('clear')->thrice()
					->call('write')->withIdenticalArguments('Error: ' . $message . PHP_EOL)->once()
				->mock($locale)->call('_')->withArguments('Error: %s')->exactly(3)
				->object($script->writeError((' ' . $message = uniqid()) . ' ' . PHP_EOL))->isIdenticalTo($script)
				->mock($stderr)
					->call('clear')->exactly(4)
					->call('write')->withIdenticalArguments('Error: ' . $message . PHP_EOL)->once()
				->mock($locale)->call('_')->withArguments('Error: %s')->exactly(4)
		;
	}

	public function testVerbose()
	{
		$this
			->if($stdOut = new mock\writers\std\out())
			->and($stdOut->getMockCOntroller()->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setOutputWriter($stdOut))
			->then
				->object($script->verbose($message = uniqid()))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->never()
			->if($script->increaseVerbosityLevel())
			->then
				->object($script->verbose($message = uniqid()))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->once()
				->object($script->verbose($message, 1))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->twice()
				->object($script->verbose($message, rand(2, PHP_INT_MAX)))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message . PHP_EOL)->twice()
				->object($script->verbose($message, 0, false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->never()
				->object($script->verbose($message, 1, false))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($message)->once()
		;
	}

	public function testClearMessage()
	{
		$this
			->if($stdOut = new mock\writers\std\out())
			->and($stdOut->getMockCOntroller()->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setOutputWriter($stdOut))
			->then
				->object($script->clearMessage($message = uniqid()))->isIdenticalTo($script)
				->mock($stdOut)->call('clear')->once()
		;
	}

	public function testWriteLabel()
	{
		$this
			->if($stdOut = new mock\writers\std\out())
			->and($stdOut->getMockCOntroller()->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setOutputWriter($stdOut))
			->then
				->object($script->writeLabel($label = uniqid(), $message = uniqid()))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($label . ': ' . $message . PHP_EOL)->once()
				->object($script->writeLabel($label, $message, 0))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($label . ': ' . $message . PHP_EOL)->exactly(2)
				->object($script->writeLabel(($label = ' ' . $label) . PHP_EOL, ' ' . $message . ' ' . PHP_EOL))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($label . ': ' . $message . PHP_EOL)->once()
				->object($script->writeLabel($label, $message, 0))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments($label . ': ' . $message . PHP_EOL)->exactly(2)
				->object($script->writeLabel($label = uniqid(), $message = uniqid(), 1))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments(atoum\script::padding . $label . ': ' . $message . PHP_EOL)->once()
				->object($script->writeLabel($label, $message, 2))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . $label . ': ' . $message . PHP_EOL)->once()
		;
	}

	public function testWriteLabels()
	{
		$this
			->if($stdOut = new mock\writers\std\out())
			->and($stdOut->getMockCOntroller()->write = function() {})
			->and($script = new mock\script(uniqid()))
			->and($script->setOutputWriter($stdOut))
			->then
				->object($script->writeLabels(array($label = uniqid() => $message = uniqid())))->isIdenticalTo($script)
				->mock($stdOut)->call('write')->withIdenticalArguments(atoum\script::padding . $label . ': ' . $message . PHP_EOL)->once()
				->object($script->writeLabels(
						array(
							$label1 = uniqid() => $message1 = uniqid(),
							$label2 = uniqid() => $message2 = uniqid(),
							$label3 = uniqid() => $message3 = uniqid()
						)
					)
				)
					->isIdenticalTo($script)
				->mock($stdOut)
					->call('write')->withIdenticalArguments(atoum\script::padding . $label1 . ': ' . $message1 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . $label2 . ': ' . $message2 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . $label3 . ': ' . $message3 . PHP_EOL)->once()
				->object($script->writeLabels(
						array(
							$label1 = uniqid() => $message1 = uniqid(),
							$label2 = '  ' . uniqid() => $message2 = uniqid(),
							$label3 = uniqid() => $message3 = uniqid()
						)
					)
				)
					->isIdenticalTo($script)
				->mock($stdOut)
					->call('write')->withIdenticalArguments(atoum\script::padding . '  ' . $label1 . ': ' . $message1 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding .        $label2 . ': ' . $message2 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . '  ' . $label3 . ': ' . $message3 . PHP_EOL)->once()
				->object($script->writeLabels(array(
							$label1 = uniqid() => $message1 = uniqid(),
							$label2 = 'xx' . uniqid() => $message2 = uniqid(),
							$label3 = uniqid() => $message3 = uniqid()
						), 3
					)
				)
					->isIdenticalTo($script)
				->mock($stdOut)
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding . '  ' . $label1 . ': ' . $message1 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding .        $label2 . ': ' . $message2 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding . '  ' . $label3 . ': ' . $message3 . PHP_EOL)->once()
				->object($script->writeLabels(array(
							$label1 = uniqid() => $message1 = uniqid(),
							$label2 = 'xx' . uniqid() => ($message21 = uniqid()) . PHP_EOL . ($message22 = uniqid()),
							$label3 = uniqid() => $message3 = uniqid()
						), 3
					)
				)
					->isIdenticalTo($script)
				->mock($stdOut)
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding . '  ' . $label1 . ': ' . $message1 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding .        $label2 . ': ' . $message21 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding . '               ' . ': ' . $message22 . PHP_EOL)->once()
					->call('write')->withIdenticalArguments(atoum\script::padding . atoum\script::padding . atoum\script::padding . '  ' . $label3 . ': ' . $message3 . PHP_EOL)->once()
		;
	}

	public function testGetDirectory()
	{
		$this
			->given($script = new mock\script($name = uniqid()))
			->and($script->setAdapter($adapter = new atoum\test\adapter()))
			->then
			->if($adapter->is_dir = true)
			->and($adapter->dirname = $directory = uniqid())
			->then
			->string($script->getDirectory())->isEqualTo($directory)
			->if($adapter->is_dir = false)
			->and($adapter->getcwd = $currentDirectory = uniqid())
			->then
			->string($script->getDirectory())->isEqualTo($currentDirectory)
			;
	}
}
