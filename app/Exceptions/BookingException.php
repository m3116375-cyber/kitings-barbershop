<?php

namespace App\Exceptions;

use RuntimeException;

/** Kesalahan bisnis yang aman ditampilkan ke pengguna (slot penuh, stok kurang, dll). */
class BookingException extends RuntimeException
{
}
