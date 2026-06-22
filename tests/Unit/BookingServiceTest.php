<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Enums\{BookingStatus, TripStatus};
use App\Models\{Booking, Trip, User};
use App\Exceptions\{SeatUnavailableException, BookingException, InvalidTransitionException};

class BookingServiceTest extends TestCase
{
    // ── Test : machine à états ─────────────────────────────
    public function testBookingTransitionPendingToConfirmed(): void
    {
        $booking = new Booking();
        $booking->setStatus(BookingStatus::PENDING);
        $history = $booking->transitionTo(BookingStatus::CONFIRMED, 1);

        $this->assertSame(BookingStatus::CONFIRMED, $booking->getStatus());
        $this->assertSame(BookingStatus::PENDING,   $history->getFromStatus());
        $this->assertSame(BookingStatus::CONFIRMED, $history->getToStatus());
    }

    public function testBookingTransitionPendingToCancelled(): void
    {
        $booking = new Booking();
        $booking->setStatus(BookingStatus::PENDING);
        $booking->transitionTo(BookingStatus::CANCELLED, 2, 'Changement de plans');
        $this->assertSame(BookingStatus::CANCELLED, $booking->getStatus());
    }

    public function testBookingInvalidTransitionThrows(): void
    {
        $this->expectException(InvalidTransitionException::class);
        $booking = new Booking();
        $booking->setStatus(BookingStatus::COMPLETED);
        $booking->transitionTo(BookingStatus::PENDING, 1);
    }

    public function testCompletedBookingCannotBeCancelled(): void
    {
        $booking = new Booking();
        $booking->setStatus(BookingStatus::COMPLETED);
        $this->assertFalse($booking->isCancellable());
    }

    public function testPendingBookingIsCancellable(): void
    {
        $booking = new Booking();
        $booking->setStatus(BookingStatus::PENDING);
        $this->assertTrue($booking->isCancellable());
    }

    // ── Test : Trip ──────────────────────────────────────
    public function testTripHasAvailableSeats(): void
    {
        $trip = new Trip();
        $trip->setStatus(TripStatus::OPEN);
        $trip->setSeatsAvailable(3);
        $this->assertTrue($trip->hasAvailableSeats(1));
        $this->assertTrue($trip->hasAvailableSeats(3));
        $this->assertFalse($trip->hasAvailableSeats(4));
    }

    public function testTripFullStatusNoSeats(): void
    {
        $trip = new Trip();
        $trip->setStatus(TripStatus::FULL);
        $trip->setSeatsAvailable(0);
        $this->assertFalse($trip->hasAvailableSeats(1));
    }

    public function testOccupancyRate(): void
    {
        $trip = new Trip();
        $trip->setSeatsOffered(4);
        $trip->setSeatsAvailable(1);
        $this->assertEquals(75.0, $trip->getOccupancyRate());
    }

    // ── Test : User ──────────────────────────────────────
    public function testUserFullName(): void
    {
        $user = new User();
        $user->setFirstName('Mamadou');
        $user->setLastName('Diallo');
        $this->assertSame('Mamadou Diallo', $user->getFullName());
    }

    public function testUserInitials(): void
    {
        $user = new User();
        $user->setFirstName('Ibrahima');
        $user->setLastName('Sow');
        $this->assertSame('IS', $user->getInitials());
    }

    // ── Test : BookingStatus enum ─────────────────────────
    public function testBookingStatusAllowedTransitions(): void
    {
        $this->assertTrue(BookingStatus::PENDING->canTransitionTo(BookingStatus::CONFIRMED));
        $this->assertTrue(BookingStatus::PENDING->canTransitionTo(BookingStatus::CANCELLED));
        $this->assertTrue(BookingStatus::CONFIRMED->canTransitionTo(BookingStatus::COMPLETED));
        $this->assertTrue(BookingStatus::CONFIRMED->canTransitionTo(BookingStatus::CANCELLED));
        $this->assertFalse(BookingStatus::COMPLETED->canTransitionTo(BookingStatus::CANCELLED));
        $this->assertFalse(BookingStatus::CANCELLED->canTransitionTo(BookingStatus::CONFIRMED));
    }

    // ── Test : QR Code ────────────────────────────────────
    public function testQrCodeGeneration(): void
    {
        $booking = new Booking();
        $qr = $booking->generateQrCode();
        $this->assertNotEmpty($qr);
        $this->assertSame(32, strlen($qr)); // 16 bytes = 32 hex chars
        $this->assertSame($qr, $booking->getQrCode());
    }
}
