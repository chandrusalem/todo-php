<?php

    function generateResetToken(): string {
        return bin2hex(random_bytes(32));
    }
    function hashResetToken(string $token): string {
        return hash("sha256", $token);
    }