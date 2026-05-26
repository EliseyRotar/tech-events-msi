<?php

namespace App;

class Discord
{
    public static function notify(string $webhookUrl, array $embeds): bool
    {
        if (empty($webhookUrl)) {
            return false;
        }

        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['embeds' => $embeds]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);
        curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode >= 200 && $httpCode < 300;
    }

    public static function matchResult(
        string $webhookUrl,
        string $tournament,
        string $team1,
        int    $score1,
        string $team2,
        int    $score2,
        string $matchUrl = ''
    ): bool {
        $winner = $score1 > $score2 ? $team1 : $team2;
        $fields = [];
        if ($matchUrl) {
            $fields[] = ['name' => 'Match Page', 'value' => $matchUrl, 'inline' => false];
        }
        return self::notify($webhookUrl, [[
            'title'       => '⚔️ Match Result',
            'description' => "**{$team1}** {$score1} — {$score2} **{$team2}**\n🏆 Winner: **{$winner}**",
            'color'       => 0x00D4FF,
            'footer'      => ['text' => $tournament],
            'timestamp'   => date('c'),
            'fields'      => $fields,
        ]]);
    }

    public static function tournamentStart(string $webhookUrl, string $tournament, int $teamCount): bool
    {
        return self::notify($webhookUrl, [[
            'title'       => '🚀 Tournament Started',
            'description' => "**{$tournament}** has kicked off with {$teamCount} teams!",
            'color'       => 0x00FF88,
            'timestamp'   => date('c'),
        ]]);
    }

    public static function tournamentWinner(string $webhookUrl, string $tournament, string $winner): bool
    {
        return self::notify($webhookUrl, [[
            'title'       => '🏆 Tournament Champion',
            'description' => "**{$winner}** has won **{$tournament}**! Congratulations!",
            'color'       => 0xFFD700,
            'timestamp'   => date('c'),
        ]]);
    }
}
