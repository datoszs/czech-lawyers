<?php declare(strict_types=1);
namespace App\Model;

use App\Model\Annulments\AnnulmentRepository;
use App\Model\Cause\CausesRepository;
use App\Model\Court\CourtsRepository;
use App\Model\Disputes\DisputeRepository;
use App\Model\Advocates\AdvocateInfosRepository;
use App\Model\Advocates\AdvocatesRepository;
use App\Model\Documents\DocumentsConstitutionalCourtRepository;
use App\Model\Documents\DocumentsRepository;
use App\Model\Documents\DocumentsSupremeAdministrativeCourtRepository;
use App\Model\Documents\DocumentsSupremeCourtRepository;
use App\Model\Documents\TaggingAdvocatesRepository;
use App\Model\Documents\TaggingCaseResultsRepository;
use App\Model\Documents\TaggingCaseSuccessesRepository;
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
 * @property-read DisputeRepository $disputes
 * @property-read AnnulmentRepository $annulments
 * @property-read DocumentsRepository $documents
 * @property-read DocumentsSupremeCourtRepository $documentsSupremeCourt
 * @property-read DocumentsSupremeAdministrativeCourtRepository $documentsSupremeAdministrativeCourt
 * @property-read DocumentsConstitutionalCourtRepository $documentsConstitutionalCourt
 * @property-read AdvocatesRepository $advocates
 * @property-read AdvocateInfosRepository $advocatesInfo
 * @property-read TaggingCaseResultsRepository $taggingCaseResults
 * @property-read TaggingCaseSuccessesRepository $taggingCaseSuccess
 * @property-read TaggingAdvocatesRepository $taggingAdvocates
 */
class Orm extends Model
{
}
