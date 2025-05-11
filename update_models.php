<?php

$models = [
    'Message.php',
    'MTurk.php',
    'Name.php',
    'Log.php',
    'Network.php',
    'Nameset.php',
    'MturkHit.php',
    'Search.php',
    'AnswerKey.php',
    'Queue.php',
    'Reply.php',
    'Factoidset.php',
    'Forward.php',
    'NetworkEdge.php',
    'Group.php',
    'Round.php',
    'Role.php',
    'Keyword.php',
    'Factoid.php',
    'NetworkNode.php',
    'Session.php',
    'UserNode.php',
    'SolutionCategory.php'
];

foreach ($models as $model) {
    $file = "app/Models/{$model}";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Update namespace
        $content = str_replace('namespace oceler;', 'namespace App\Models;', $content);
        
        // Add Laravel 11 features
        $content = str_replace(
            'use Illuminate\Database\Eloquent\Model;',
            "use Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;",
            $content
        );
        
        // Add HasFactory trait
        $content = preg_replace(
            '/class (\w+) extends Model\s*{/',
            "class $1 extends Model\n{\n    use HasFactory;\n",
            $content
        );
        
        // Update relationship return types
        $content = preg_replace(
            '/public function (\w+)\(\)\s*{/',
            'public function $1(): \Illuminate\Database\Eloquent\Relations\\$1 {',
            $content
        );
        
        // Update model references
        $content = str_replace("'oceler\\", "'App\\Models\\", $content);
        $content = str_replace('"oceler\\', '"App\\Models\\', $content);
        
        file_put_contents($file, $content);
    }
} 