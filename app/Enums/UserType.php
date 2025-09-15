<?php

namespace App\Enums;

enum UserType: int
{
    case Owner = 10;
    case Senior = 11;
    case Master = 15;
    case Agent = 20;
    case SubAgent = 30;
    case Player = 40;
    case SystemWallet = 50;

    public static function usernameLength(UserType $type): int
    {
        return match ($type) {
            self::Owner => 1,
            self::Senior => 2,
            self::Master => 3,
            self::Agent => 4,
            self::SubAgent => 5,
            self::Player => 6,
            self::SystemWallet => 7,
        };
    }

    public static function childUserType(UserType $type): UserType
    {
        return match ($type) {
            self::Owner => self::Agent,
            self::Senior => self::Agent,
            self::Master => self::Agent,
            self::Agent => self::SubAgent,
            self::SubAgent => self::Player,
            self::Player, self::SystemWallet => self::Player,
        };
    }

    public static function canHaveChild(UserType $parent, UserType $child): bool
    {
        return match ($parent) {
            self::Owner => $child === self::Agent || $child === self::Senior || $child === self::Master,
            self::Senior => $child === self::Agent || $child === self::Master,
            self::Master => $child === self::Agent,
            self::Agent => $child === self::SubAgent || $child === self::Player,
            self::SubAgent => $child === self::Player,
            self::Player, self::SystemWallet => false,
        };
    }
}
