<?php
/**
 * Redis backend for storing failed Resque jobs.
 *
 * @package		Resque/Failure
 * @author		Chris Boulton <chris@bigcommerce.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

class Resque_Failure_Redis implements Resque_Failure_Interface
{
	/**
	 * Initialize a failed job class and save it (where appropriate).
	 *
	 * @param object $job Job that failed.
	 * @param object $exception Instance of the exception that was thrown by the failed job.
	 * @param object $worker Instance of Resque_Worker that received the job.
	 * @param string $queue The name of the queue the job was fetched from.
	 */
	public function __construct($job, $exception, $worker, $queue)
	{
		$data = json_encode($this->getData($job, $exception, $worker, $queue));
		Resque::redis()->rpush('failed', $data);
	}

	protected function getData($job, $exception, $worker, $queue) {
		$data = new stdClass;
		$data->failed_at = strftime('%a %b %d %H:%M:%S %Z %Y');
		$data->payload = $job->payload;
		$data->exception = get_class($exception);
		$data->error = $exception->getMessage();
		$data->backtrace = explode("\n", $exception->getTraceAsString());
		$data->worker = (string)$worker;
		$data->queue = $queue;

		return $data;
	}
}
?>