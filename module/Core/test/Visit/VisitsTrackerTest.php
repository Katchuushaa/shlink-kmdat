<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Core\Visit;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shlinkio\Shlink\Core\EventDispatcher\Event\UrlVisited;
use Shlinkio\Shlink\Core\Options\TrackingOptions;
use Shlinkio\Shlink\Core\ShortUrl\Entity\ShortUrl;
use Shlinkio\Shlink\Core\Visit\Entity\Visit;
use Shlinkio\Shlink\Core\Visit\Model\Visitor;
use Shlinkio\Shlink\Core\Visit\VisitsTracker;

class VisitsTrackerTest extends TestCase
{
    use ProphecyTrait;

    private VisitsTracker $visitsTracker;
    private ObjectProphecy $em;
    private ObjectProphecy $eventDispatcher;

    protected function setUp(): void
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    /**
     * @test
     * @dataProvider provideTrackingMethodNames
     */
    public function trackPersistsVisitAndDispatchesEvent(string $method, array $args): void
    {
        $persist = $this->em->persist(Argument::that(fn (Visit $visit) => $visit->setId('1')))->will(function (): void {
        });

        $this->visitsTracker()->{$method}(...$args);

        $persist->shouldHaveBeenCalledOnce();
        $this->em->flush()->shouldHaveBeenCalledOnce();
        $this->eventDispatcher->dispatch(Argument::type(UrlVisited::class))->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider provideTrackingMethodNames
     */
    public function trackingIsSkippedCompletelyWhenDisabledFromOptions(string $method, array $args): void
    {
        $this->visitsTracker(new TrackingOptions(disableTracking: true))->{$method}(...$args);

        $this->eventDispatcher->dispatch(Argument::cetera())->shouldNotHaveBeenCalled();
        $this->em->persist(Argument::cetera())->shouldNotHaveBeenCalled();
        $this->em->flush()->shouldNotHaveBeenCalled();
    }

    public function provideTrackingMethodNames(): iterable
    {
        yield 'track' => ['track', [ShortUrl::createEmpty(), Visitor::emptyInstance()]];
        yield 'trackInvalidShortUrlVisit' => ['trackInvalidShortUrlVisit', [Visitor::emptyInstance()]];
        yield 'trackBaseUrlVisit' => ['trackBaseUrlVisit', [Visitor::emptyInstance()]];
        yield 'trackRegularNotFoundVisit' => ['trackRegularNotFoundVisit', [Visitor::emptyInstance()]];
    }

    /**
     * @test
     * @dataProvider provideOrphanTrackingMethodNames
     */
    public function orphanVisitsAreNotTrackedWhenDisabled(string $method): void
    {
        $this->visitsTracker(new TrackingOptions(trackOrphanVisits: false))->{$method}(Visitor::emptyInstance());

        $this->eventDispatcher->dispatch(Argument::cetera())->shouldNotHaveBeenCalled();
        $this->em->persist(Argument::cetera())->shouldNotHaveBeenCalled();
        $this->em->flush()->shouldNotHaveBeenCalled();
    }

    public function provideOrphanTrackingMethodNames(): iterable
    {
        yield 'trackInvalidShortUrlVisit' => ['trackInvalidShortUrlVisit'];
        yield 'trackBaseUrlVisit' => ['trackBaseUrlVisit'];
        yield 'trackRegularNotFoundVisit' => ['trackRegularNotFoundVisit'];
    }

    private function visitsTracker(?TrackingOptions $options = null): VisitsTracker
    {
        return new VisitsTracker(
            $this->em->reveal(),
            $this->eventDispatcher->reveal(),
            $options ?? new TrackingOptions(),
        );
    }
}
