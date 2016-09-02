<?php
namespace App\Model;

use App\Model\Cause\CausesRepository;
use App\Model\Court\CourtsRepository;
use App\Model\Documents\DocumentsRepository;
use App\Model\Documents\DocumentsSupremeCourtRepository;
use App\Model\Jobs\JobRunsRepository;
use App\Model\Jobs\JobsRepository;
use App\Model\Users\UsersRepository;
use Nextras\Orm\Model\Model;

/**
 * @property-read UsersRepository $users
 * @property-read JobsRepository $jobs
 * @property-read JobRunsRepository $jobRuns
 * @property-read CourtsRepository $courts
 * @property-read CausesRepository $causes
 * @property-read DocumentsRepository $documents
 * @property-read DocumentsSupremeCourtRepository $documentsSupremeCourt
 */
class Orm extends Model
{
}