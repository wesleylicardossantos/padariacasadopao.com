<?php
// Cole APENAS esta linha dentro do método schedule(Schedule $schedule) no app/Console/Kernel.php

$schedule->command('rh:ia-automatica-diaria')->dailyAt('08:00');
