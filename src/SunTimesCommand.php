<?php
namespace Marallyn\Command;

use DateInterval;
use DateTime;
use DateTimeZone;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include "includes/cli-colors.php";

#[AsCommand(
    name: 'app:sun-times',
    description: 'See some sun times.',
    hidden: false,
)]

class SunTimesCommand extends Command
{
    /** @var non-empty-array<string> */
    private array $sunKeys;
    
    /** @var non-empty-array<string, array<string, mixed>> */
    private array $locations;

    protected function configure(): void
    {
        $this->locations = $this->getLocations();
        $this->sunKeys = $this->getSunKeys();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateString = 'now';

        foreach ($this->locations as $location) {
            $timeZone = new DateTimeZone($location['timeZone']);
            $now = new DateTime($dateString, $timeZone);
            $yesterday = (new DateTime($dateString, $timeZone))->sub(new DateInterval("P1D"));

            $output->writeln(
                sprintf(
                    "%sSun times for %s%s%s on %s%s%s",
                    YELLOW,
                    GREEN,
                    $location['name'],
                    RESET,
                    BLUE,
                    $now->format('m/d/Y'),
                    RESET
                )
            );
            $sunTimes = date_sun_info($now->getTimestamp(), $location['lat'], $location['lng']);
            $sunTimesY = date_sun_info($yesterday->getTimestamp(), $location['lat'], $location['lng']);
        
            foreach ($this->sunKeys as $sunKey) {
                $timeObj = new DateTime('', $timeZone);
                $timeObj->setTimestamp(intval($sunTimes[$sunKey]));
        
                $secondsDifference = $sunTimes[$sunKey] - $sunTimesY[$sunKey] - 24 * 3600;
        
                $differenceDirection = $secondsDifference > 0 ? 'later' : 'earlier';
                $differenceColor = $secondsDifference > 0 ? RED : GREEN;
                $secondsDifference = abs($secondsDifference);
        
                $differenceText = sprintf(
                    "%s%02d:%02d %s%s",
                    $differenceColor,
                    floor($secondsDifference / 60),
                    $secondsDifference % 60,
                    $differenceDirection,
                    RESET
                );
        
                $output->writeln(
                    sprintf(
                        "%-27s %s, %s",
                        $sunKey,
                        $timeObj->format('H:i:s'),
                        $differenceText,
                    )
                );
            }

            // assume the difference is only a matter of minutes
            $usefulStart = (new DateTime('', $timeZone))->setTimestamp(intval($sunTimes['civil_twilight_begin']));
            $usefulEnd = (new DateTime('', $timeZone))->setTimestamp(intval($sunTimes['civil_twilight_end']));
            $usefulInterval = $usefulEnd->diff($usefulStart);
            $usefulIntervalSeconds = $usefulInterval->i * 60 + $usefulInterval->s;
        
            // assume the difference is only a matter of minutes
            $usefulStartY = (new DateTime('', $timeZone))->setTimestamp(intval($sunTimesY['civil_twilight_begin']));
            $usefulEndY = (new DateTime('', $timeZone))->setTimestamp(intval($sunTimesY['civil_twilight_end']));
            $usefulIntervalY = $usefulEndY->diff($usefulStartY);
            $usefulIntervalSecondsY = $usefulIntervalY->i * 60 + $usefulIntervalY->s;
        
            $usefulDifference = $usefulIntervalSeconds - $usefulIntervalSecondsY;
        
            $differenceDirection = $usefulDifference > 0 ? 'longer' : 'shorter';
            $differenceColor = $usefulDifference > 0 ? GREEN : RED;
            $usefulDifference = abs($usefulDifference);
            $differenceText = sprintf(
                "%s%02d:%02d %s%s",
                $differenceColor,
                floor($usefulDifference / 60),
                $usefulDifference % 60,
                $differenceDirection,
                RESET
            );
        
            $output->writeln(
                sprintf(
                    "%-27s %s%s%s, %s\n",
                    'Useful daylight',
                    PURPLE,
                    $usefulInterval->format('%H:%I:%S'),
                    RESET,
                    $differenceText
                )
            );
        }
        return Command::SUCCESS;
    }

    /** @return non-empty-array<string, array<string, mixed>> */
    private function getLocations(): array
    {
        return [
            'nome' => [
                'name' => 'Nome',
                'lat' => 64.530349,
                'lng' => -165.393961,
                'timeZone' => 'America/Anchorage',
            ],
            'detroit' => [
                'name' => 'Detroit',
                'lat' => 42.369294,
                'lng' => -83.105351,
                'timeZone' => 'America/New_York',
            ],
            'quito' => [
                'name' => 'Quito',
                'lat' => -0.220000,
                'lng' => -78.512500,
                'timeZone' => 'America/New_York',
            ],
            'mps' => [
                'name' => 'MPS EST',
                'lat' => 12.9658227,
                'lng' => 77.5979161,
                'timeZone' => 'America/New_York',
            ],
            'mpsi' => [
                'name' => 'MPS IST',
                'lat' => 12.9658227,
                'lng' => 77.5979161,
                'timeZone' => 'Asia/Kolkata',
            ],
            // 'dodo' => [
            //     'name' => "Dodo's house",
            //     'lat' => 40.908116,
            //     'lng' => -81.343217,
            //     'timeZone' => 'America/New_York',
            // ],
            // 'rsc' => [
            //     'name' => 'Research Square',
            //     'lat' => 35.997846,
            //     'lng' => -78.906385,
            //     'timeZone' => 'America/New_York',
            // ],
            'home' => [
                'name' => 'Home',
                'lat' => 32.950175,
                'lng' => -84.970118,
                'timeZone' => 'America/New_York',
            ],
        ];
    }

    /** @return non-empty-array<string> */
    private function getSunKeys(): array
    {
        return [
            // 'astronomical_twilight_begin',
            // 'nautical_twilight_begin',
            'civil_twilight_begin',
            'sunrise',
            'transit',
            'sunset',
            'civil_twilight_end',
            // 'nautical_twilight_end',
            // 'astronomical_twilight_end',
        ];
    }
}
