<?php declare(strict_types=1);
namespace App\Model\Services;

use App\Model\Orm;
use Nextras\Dbal\Connection;

class StatisticsService
{

	/** @var Orm */
	private $orm;

	/** @var Connection */
	private $connection;

	public function __construct(Orm $orm, Connection $connection)
	{
		$this->orm = $orm;
		$this->connection = $connection;
	}

	public function caseCountPerYear(): array
	{
		return $this->connection->query('select year, court_id, COUNT(*) from "case" WHERE TRUE GROUP BY year, court_id ORDER BY court_id ASC, year ASC')->fetchAll();
	}

	public function caseCount(): array
	{
		return $this->connection->query('select court_id, COUNT(*) from "case" WHERE TRUE GROUP BY court_id ORDER BY court_id ASC')->fetchAll();
	}

	public function officialDataPerYearCount(): array
	{
		return $this->connection->query('select year, court_id, COUNT(*) from "case" WHERE official_data IS NOT NULL GROUP BY year, court_id ORDER BY court_id ASC,year ASC')->fetchAll();
	}

	public function officialDataCount(): array
	{
		return $this->connection->query('select court_id, COUNT(*) from "case" WHERE official_data IS NOT NULL GROUP BY court_id ORDER BY court_id ASC')->fetchAll();
	}

	public function countLatestAdvocateTaggingsByStatus(): array
	{
		return $this->connection->query('
			select status, count(*)
			from latest_tagging_advocate
			join tagging_advocate ON latest_tagging_advocate.tagging_advocate_id = tagging_advocate.id_tagging_advocate
			group by status
		')->fetchAll();
	}

	public function countLatestCaseResultTaggingsByStatus(): array
	{
		return $this->connection->query('
			select status, count(*)
			from latest_tagging_case_result
			join tagging_case_result ON latest_tagging_case_result.tagging_case_result_id = tagging_case_result.id_tagging_case_result
			group by status
		')->fetchAll();
	}

	public function countAdvocates(): array
	{
		return $this->connection->query('select count(*) from advocate')->fetchAll();
	}

	public function countDocuments(): array
	{
		return $this->connection->query('select court_id, count(*) from document group by court_id order by court_id ASC')->fetchAll();
	}

	public function countDocumentsPerYear(): array
	{
		return $this->connection->query('
			select year, document.court_id, count(*) from document
			join "case" on "case".id_case = document.case_id
			group by document.court_id, year order by court_id ASC, year ASC
		')->fetchAll();
	}
}
