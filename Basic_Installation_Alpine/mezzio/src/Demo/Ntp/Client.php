<?php
namespace Demo\Ntp;

use Closure;
use DateTime;
use RuntimeException;
use Bt51\NTP\Socket;
use Bt51\NTP\Client as Bt51Client;
class Client
{
    const NTP_SVR = [0,1,2,3];
    const NTP_POOL = '.pool.ntp.org';
    const NTP_PORT = 123;
    /**
     * Makes request to NTP server
     *
     * @return DateTime $result : result
     */
    public function getTime() : DateTime
    {
        $svr = self::NTP_SVR[array_rand(self::NTP_SVR)];
        $socket = new Socket($svr . self::NTP_POOL, self::NTP_PORT);
        $ntp = new Bt51Client($socket);
        $time = $ntp->getTime();
        return $time;
    }
    public function getCallback()
    {
        return Closure::fromCallable([$this, 'getTime']);
    }
    public function __invoke() : DateTime
    {
        return $this->getTime();
    }
}
