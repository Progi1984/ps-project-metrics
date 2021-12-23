<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Command;

use App\Helper\DayComputer;
use App\Helper\ReviewRecordService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use DateTime;

class RecordReviewsCommand extends Command
{
    /**
     * @var ReviewRecordService
     */
    private ReviewRecordService $recordService;

    /** @var string */
    protected static $defaultName = 'matks:review-stats:record';

    /**
     * @param ReviewRecordService $recordService
     */
    public function __construct(ReviewRecordService $recordService)
    {
        $this->recordService = $recordService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_OPTIONAL,
                'Dry run'
            )
            ->addOption(
                'no-weekend',
                null,
                InputOption::VALUE_OPTIONAL,
                'Dont run on weekend'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRunOption = $input->getOption('dry-run');
        $isDryRun = ('false' !== $dryRunOption);

        $noWeekendRunOption = $input->getOption('no-weekend');
        $noWeekendRun = ('true' === $noWeekendRunOption);

        $day = new DateTime();

        if (DayComputer::isItWeekend($day) && $noWeekendRun) {
            $output->writeln('No run on the weekend');
            return 0;
        }

        $previousWorkedDay = DayComputer::getPreviousWorkedDayFromDateTime($day);
        $output->writeln(sprintf(
            'Record reviews for %s (dry-run: %s)',
            $previousWorkedDay->format('Y-m-d'),
            ($isDryRun ? '<info>true</info>' : '<error>false</error>')
        ));

        $recordLogs = $this->recordService->recordReviewsForDay($previousWorkedDay, $isDryRun);

        foreach ($recordLogs as $log) {
            $output->writeln($log);
        }

        return 0;
    }
}
