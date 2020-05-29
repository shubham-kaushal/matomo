<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin;

use Piwik\Date;
use Piwik\Option;

/**
 * Class Archiver
 *
 * Archiver is class processing raw data into ready ro read reports.
 * It must implement two methods for aggregating daily reports
 * aggregateDayReport() and other for summing daily reports into periods
 * like week, month, year or custom range aggregateMultipleReports().
 *
 * For more detailed information about Archiver please visit Piwik developer guide
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    /**
     * It is a good practice to store your archive names (reports stored in database)
     * in Archiver class constants. You can define as many record names as you want
     * for your plugin.
     *
     * Also important thing is that record name must be prefixed with plugin name.
     *
     * This is only an example record name, so feel free to change it to suit your needs.
     */
    const EXAMPLEPLUGIN_ARCHIVE_RECORD = "ExamplePlugin_archive_record";
    const EXAMPLEPLUGIN_METRIC_NAME = 'ExamplePlugin_example_metric';
    const EXAMPLEPLUGIN_CONST_METRIC_NAME = 'ExamplePlugin_example_metric2';

    private $daysFrom = '2016-07-08';

    public function aggregateDayReport()
    {
        /**
         * inside this method you can implement your LogAggreagator usage
         * to process daily reports, this one uses idvisitor to group results.
         *
         * $visitorMetrics = $this
         * ->getLogAggregator()
         * ->getMetricsFromVisitByDimension('idvisitor')
         * ->asDataTable();
         * $visitorReport = $visitorMetrics->getSerialized();
         * $this->getProcessor()->insertBlobRecord(self::EXAMPLEPLUGIN_ARCHIVE_RECORD, $visitorReport);
         */

        if ($this->getProcessor()->isArchiving(self::EXAMPLEPLUGIN_METRIC_NAME)) {
            // insert a test numeric metric that is the difference in days between the day we're archiving and
            // $this->daysFrom.
            $daysFrom = Date::factory($this->daysFrom);
            $date = $this->getProcessor()->getParams()->getPeriod()->getDateStart();

            $differenceInSeconds = $daysFrom->getTimestamp() - $date->getTimestamp();
            $differenceInDays = round($differenceInSeconds / 86400);

            $this->getProcessor()->insertNumericRecord(self::EXAMPLEPLUGIN_METRIC_NAME, $differenceInDays);
        }

        if ($this->getProcessor()->isArchiving(self::EXAMPLEPLUGIN_CONST_METRIC_NAME)) {
            $archiveCount = $this->incrementArchiveCount();
            $this->getProcessor()->insertNumericRecord(self::EXAMPLEPLUGIN_CONST_METRIC_NAME, 50 + $archiveCount);
        }
    }

    public function aggregateMultipleReports()
    {
        /**
         * Inside this method you can simply point daily records
         * to be summed. This work for most cases.
         * However if needed, also custom queries can be implemented
         * for periods to achieve more acurrate results.
         *
         * $this->getProcessor()->aggregateDataTableRecords(self::EXAMPLEPLUGIN_ARCHIVE_RECORD);
         */

        $this->getProcessor()->aggregateNumericMetrics([self::EXAMPLEPLUGIN_METRIC_NAME]);
    }

    private function incrementArchiveCount()
    {
        $archiveCount = Option::get('ExamplePlugin_archiveCount') ?: 0;
        $archiveCount += 1;
        Option::set('ExamplePlugin_archiveCount', $archiveCount);
        return $archiveCount;
    }
}
