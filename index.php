<?php
date_default_timezone_set('Asia/Jakarta'); 

// Jadwal event: atur mulai & selesai
$eventSchedule = [
    'Monday'    => ['startHour' => null, 'startMinute' => null, 'endHour' => null, 'endMinute' => null],
    'Tuesday'    => ['startHour' => null, 'startMinute' => null, 'endHour' => null, 'endMinute' => null],
    'Wednesday' => ['startHour' => 15, 'startMinute' => 0,  'endHour' => 15, 'endMinute' => 30],
    'Friday'    => ['startHour' => null, 'startMinute' => null, 'endHour' => null, 'endMinute' => null],
];

// Mapping English → Indonesia
$dayMap = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Event Countdown</title>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; background: #f0f0f0; }
    .hidden { display: none; }
    #countdownPage, #eventPage { padding: 40px; }
    .timer { font-size: 32px; font-weight: bold; color: #333; margin-top: 20px; }
    button { padding: 10px 20px; cursor: pointer; margin-top: 20px; }
  </style>
</head>
<body>

<!-- COUNTDOWN PAGE -->
<div id="countdownPage">
  <h1>Event Akan Dimulai</h1>
  <p id="nextEventInfo"></p>
  <div class="timer" id="countdown"></div>
  <br>
  <!-- Tombol Skip -->
  <button onclick="skipEvent()">Skip</button>
</div>

<!-- EVENT PAGE -->
<div id="eventPage" class="hidden">
  <h1>🎉 Event Sedang Berlangsung!</h1>

  <!-- ===================================================== -->
  <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Hadiah Gratis - Sistem Stok</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { box-sizing: border-box; }
        .gift-animation { animation: bounce 2s infinite; }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .pulse-glow { animation: pulseGlow 2s infinite; }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 via-blue-600 to-indigo-800 min-h-screen">

<?php
// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$host = '153.92.9.1';
$dbname = 'u1659760_astore';
$username = 'u1659760_astore';
$password = 'Q3!m0xsR';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

// Helper Functions
function sendResponse($success, $message, $data = null) {
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = array(
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    );
    
    echo json_encode($response);
    exit;
}

function getClientIP() {
    $ipKeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
}

// Handle AJAX requests only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if (!$pdo) {
        sendResponse(false, 'Database connection error. Please try again later.');
    }
    
    switch ($action) {
        case 'claimPrize':
            $token = strtoupper(trim(isset($_POST['token']) ? $_POST['token'] : ''));
            
            if (!$token) {
                sendResponse(false, 'Token required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, token, stock, used_count, (stock - used_count) as remaining FROM tokens WHERE token = ? AND (stock - used_count) > 0");
                $stmt->execute([$token]);
                $tokenData = $stmt->fetch();
                
                if (!$tokenData) {
                    sendResponse(false, 'Token tidak valid atau stok sudah habis!');
                }
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM prizes WHERE (stock - claimed_count) > 0");
                $stmt->execute();
                $prizeCount = $stmt->fetch()['count'];
                
                if ($prizeCount == 0) {
                    sendResponse(false, 'Maaf, semua hadiah sudah habis!');
                }
                
                $pdo->beginTransaction();
                
                try {
                    $stmt = $pdo->prepare("SELECT id, name, icon FROM prizes WHERE (stock - claimed_count) > 0 ORDER BY RAND() LIMIT 1");
                    $stmt->execute();
                    $prize = $stmt->fetch();
                    
                    if (!$prize) {
                        throw new Exception('Tidak ada hadiah tersedia');
                    }
                    
                    $stmt = $pdo->prepare("UPDATE tokens SET used_count = used_count + 1 WHERE id = ?");
                    $stmt->execute([$tokenData['id']]);
                    
                    $stmt = $pdo->prepare("UPDATE prizes SET claimed_count = claimed_count + 1 WHERE id = ?");
                    $stmt->execute([$prize['id']]);
                    
                    $stmt = $pdo->prepare("INSERT INTO winners (token_id, prize_id, token_used, prize_name, prize_icon, ip_address, user_agent, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    $stmt->execute([
                        $tokenData['id'],
                        $prize['id'],
                        $token,
                        $prize['name'],
                        $prize['icon'],
                        getClientIP(),
                        isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
                    ]);
                    
                    $winnerId = $pdo->lastInsertId();
                    
                    $pdo->commit();
                    
                    sendResponse(true, 'Hadiah berhasil diklaim!', [
                        'prize' => $prize,
                        'winner_id' => $winnerId
                    ]);
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    sendResponse(false, 'Error saat memproses: ' . $e->getMessage());
                }
                
            } catch (Exception $e) {
                sendResponse(false, 'Database error: ' . $e->getMessage());
            }
            break;
            
        case 'updateGameId':
            $winnerId = isset($_POST['winner_id']) ? intval($_POST['winner_id']) : 0;
            $gameId = trim(isset($_POST['game_id']) ? $_POST['game_id'] : '');
            
            if (!$winnerId || !$gameId) {
                sendResponse(false, 'Winner ID and Game ID required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM winners WHERE game_id = ? AND id != ?");
                $stmt->execute([$gameId, $winnerId]);
                $existing = $stmt->fetch()['count'];
                
                if ($existing > 0) {
                    sendResponse(false, 'ID Game ini sudah pernah digunakan!');
                }
                
                $stmt = $pdo->prepare("UPDATE winners SET game_id = ?, status = 'completed', updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$gameId, $winnerId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    sendResponse(true, 'Game ID berhasil disimpan!');
                } else {
                    sendResponse(false, 'Winner ID tidak valid!');
                }
                
            } catch (Exception $e) {
                sendResponse(false, 'Database error: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

// Ambil statistik untuk ditampilkan
$stats = null;
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT 
            (SELECT COALESCE(SUM(stock - used_count), 0) FROM tokens) as total_token_stock,
            (SELECT COALESCE(SUM(stock - claimed_count), 0) FROM prizes) as total_prize_stock,
            (SELECT COUNT(*) FROM winners) as total_winners,
            (SELECT COUNT(*) FROM winners WHERE status = 'completed') as completed_claims");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stats = null;
    }
}
?>

    <!-- Halaman Input Token -->
    <div id="tokenPage" class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden fade-in">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 text-center pulse-glow">
                <div class="text-6xl mb-4 gift-animation">🎁</div>
                <h1 class="text-2xl font-bold text-white mb-2">CLAIM HADIAH GRATIS</h1>
                <p class="text-green-100">Sistem dengan stok terbatas</p>
            </div>
            
            <div class="p-6">
                <!-- Statistik Stok dari Database -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
                    <h3 class="font-bold text-blue-800 mb-3">📊 Status Stok Real-time</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-white rounded-lg p-2 text-center">
                            <div class="font-bold text-green-600"><?= $stats ? $stats['total_token_stock'] : '25' ?></div>
                            <div class="text-gray-600">Token Tersisa</div>
                        </div>
                        <div class="bg-white rounded-lg p-2 text-center">
                            <div class="font-bold text-purple-600"><?= $stats ? $stats['total_prize_stock'] : '15' ?></div>
                            <div class="text-gray-600">Hadiah Tersisa</div>
                        </div>
                        <div class="bg-white rounded-lg p-2 text-center">
                            <div class="font-bold text-orange-600"><?= $stats ? $stats['total_winners'] : '8' ?></div>
                            <div class="text-gray-600">Total Pemenang</div>
                        </div>
                        <div class="bg-white rounded-lg p-2 text-center">
                            <div class="font-bold text-indigo-600"><?= $stats ? $stats['completed_claims'] : '5' ?></div>
                            <div class="text-gray-600">Klaim Selesai</div>
                        </div>
                    </div>
                </div>
                
                <!-- Pertanyaan Token -->
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-6">
                    <h3 class="font-bold text-yellow-800 mb-3">❓ Pertanyaan Token</h3>
                    <p class="text-sm text-yellow-700 mb-3">Jawab pertanyaan berikut untuk mendapatkan hadiah:</p>
                    <div class="bg-white rounded-lg p-3 mb-3">
                        <p class="font-semibold text-gray-800">Siapa nama lengkap admin yang mengelola event ini?</p>
                    </div>
                    <p class="text-xs text-yellow-600 mb-2">💡 Hint: Jawaban ini adalah token Anda</p>
                    <p class="text-xs text-red-600">⚠️ Setiap token memiliki stok terbatas!</p>
                </div>
                
                <form id="tokenForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban / Token</label>
                        <input type="text" id="tokenInput" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-center text-lg font-mono" placeholder="Masukkan jawaban Anda">
                    </div>
                    
                    <button type="submit" id="submitBtn" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl text-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        🎁 CLAIM HADIAH SEKARANG!
                    </button>
                </form>
                
                <div class="mt-6 p-4 bg-green-50 rounded-xl">
                    <h3 class="font-semibold text-green-800 mb-2">🎊 Sistem Stok:</h3>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>• Setiap token memiliki stok terbatas</li>
                        <li>• Hadiah berkurang otomatis saat diklaim</li>
                        <li>• Sistem menolak jika stok habis</li>
                        <li>• Stok diupdate real-time di database</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 text-center">
            <div class="text-4xl spin mb-4">🎰</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Mengundi Hadiah...</h3>
            <p class="text-gray-600">Mengecek stok dan memproses klaim...</p>
        </div>
    </div>

    <!-- Result Modal -->
    <div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 text-center max-w-sm mx-4">
            <div id="resultIcon" class="text-6xl mb-4"></div>
            <h3 id="resultTitle" class="text-2xl font-bold mb-2"></h3>
            <p id="resultMessage" class="text-gray-600 mb-6"></p>
            <button id="continueBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-colors">Lanjutkan</button>
        </div>
    </div>

    <!-- Halaman Input ID Game -->
    <div id="gameIdPage" class="container mx-auto px-4 py-8 hidden">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden fade-in">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 text-center">
                <div class="text-4xl mb-4">🎉</div>
                <h1 class="text-2xl font-bold text-white mb-2">Selamat!</h1>
                <p class="text-green-100">Anda sudah terdaftar sebagai pemenang</p>
            </div>
            
            <div class="p-6">
                <div id="prizeDisplay" class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-6 text-center">
                    <div id="prizeIcon" class="text-4xl mb-2"></div>
                    <h3 class="text-lg font-bold text-gray-800" id="prizeName"></h3>
                    <p class="text-sm text-gray-600">Hadiah yang Anda menangkan</p>
                </div>
                
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
                    <h3 class="font-semibold text-blue-800 mb-2">✅ Status Pemenang</h3>
                    <p class="text-sm text-blue-700">Data Anda sudah tersimpan di database. Stok hadiah dan token sudah otomatis berkurang. Masukkan ID Game untuk finalisasi.</p>
                </div>
                
                <form id="gameIdForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Game</label>
                        <input type="text" id="gameId" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan ID Game Anda">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105">📤 FINALISASI KLAIM HADIAH</button>
                </form>
                
                <button id="backBtn" class="w-full mt-4 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-xl transition-colors">← Kembali ke Halaman Utama</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 text-center max-w-sm mx-4">
            <div class="text-6xl mb-4">✅</div>
            <h3 class="text-2xl font-bold text-green-600 mb-2">Berhasil!</h3>
            <p class="text-gray-600 mb-6">Klaim hadiah Anda telah tersimpan di database dengan sistem stok otomatis. Tim kami akan memproses dalam 1x24 jam.</p>
            <button id="finishBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-xl transition-colors">Selesai</button>
        </div>
    </div>

    <script>
        // Global variables
        let currentPrize = null;
        let currentWinnerId = null;
        let isProcessing = false;
        
        // Token form handler dengan AJAX ke database
        document.getElementById('tokenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isProcessing) {
                return false;
            }
            
            const token = document.getElementById('tokenInput').value.trim().toUpperCase();
            
            if (!token) {
                alert('Mohon masukkan jawaban/token!');
                return false;
            }
            
            isProcessing = true;
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Memproses...';
            submitBtn.disabled = true;
            
            // Show loading
            document.getElementById('loadingModal').classList.remove('hidden');
            
            // AJAX request ke database dengan fetch
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=claimPrize&token=' + encodeURIComponent(token)
            })
            .then(response => response.json())
            .then(result => {
                // Hide loading
                document.getElementById('loadingModal').classList.add('hidden');
                
                isProcessing = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                console.log('Response:', result);
                
                if (result.success && result.data && result.data.prize) {
                    currentPrize = result.data.prize;
                    currentWinnerId = result.data.winner_id;
                    showResult(
                        result.data.prize.icon, 
                        'SELAMAT!', 
                        `Anda mendapat ${result.data.prize.name}! Stok otomatis berkurang di database.`, 
                        true
                    );
                } else {
                    showResult('❌', 'Gagal!', result.message || 'Token tidak valid!', false);
                }
            })
            .catch(error => {
                // Hide loading
                document.getElementById('loadingModal').classList.add('hidden');
                
                isProcessing = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                console.error('Error:', error);
                showResult('❌', 'Error!', 'Koneksi bermasalah', false);
            });
            
            return false;
        });
        
        // Show result function
        function showResult(icon, title, message, isWin) {
            document.getElementById('resultIcon').textContent = icon;
            document.getElementById('resultTitle').textContent = title;
            document.getElementById('resultMessage').textContent = message;
            
            const continueBtn = document.getElementById('continueBtn');
            if (isWin) {
                continueBtn.textContent = 'Input ID Game';
                continueBtn.onclick = () => {
                    document.getElementById('resultModal').classList.add('hidden');
                    showGameIdPage();
                };
            } else {
                continueBtn.textContent = 'Coba Lagi';
                continueBtn.onclick = () => {
                    document.getElementById('resultModal').classList.add('hidden');
                    document.getElementById('tokenInput').value = '';
                    document.getElementById('tokenInput').focus();
                };
            }
            
            document.getElementById('resultModal').classList.remove('hidden');
        }
        
        // Show game ID page
        function showGameIdPage() {
            document.getElementById('tokenPage').classList.add('hidden');
            document.getElementById('gameIdPage').classList.remove('hidden');
            
            if (currentPrize) {
                document.getElementById('prizeIcon').textContent = currentPrize.icon;
                document.getElementById('prizeName').textContent = currentPrize.name;
            }
        }
        
        // Game ID form handler dengan AJAX ke database
        document.getElementById('gameIdForm').addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const gameId = document.getElementById('gameId').value.trim();
            
            if (!gameId) {
                alert('Mohon isi ID Game!');
                return false;
            }
            
            // AJAX request dengan fetch
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=updateGameId&winner_id=' + encodeURIComponent(currentWinnerId) + '&game_id=' + encodeURIComponent(gameId)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    alert('Error: ' + (result.message || 'Terjadi kesalahan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Koneksi bermasalah');
            });
            
            return false;
        });
        
        // Back button handler
        document.getElementById('backBtn').addEventListener('click', function() {
            document.getElementById('gameIdPage').classList.add('hidden');
            document.getElementById('tokenPage').classList.remove('hidden');
            document.getElementById('tokenInput').value = '';
            currentPrize = null;
            currentWinnerId = null;
        });
        
        // Finish button handler
        document.getElementById('finishBtn').addEventListener('click', function() {
            document.getElementById('successModal').classList.add('hidden');
            document.getElementById('gameIdPage').classList.add('hidden');
            document.getElementById('tokenPage').classList.remove('hidden');
            
            // Reset forms
            document.getElementById('tokenInput').value = '';
            document.getElementById('gameId').value = '';
            currentPrize = null;
            currentWinnerId = null;
            
            // Focus ke input token
            document.getElementById('tokenInput').focus();
        });
        
        // Focus pada input token saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('tokenInput').focus();
        });
    </script>

<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9872406b60e61333',t:'MTc1OTIxOTkwOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>

  <!-- ===================================================== -->

</div>

<script>
// Mapping hari ke Indonesia
const dayMap = {
  "Monday": "Senin",
  "Tuesday": "Selasa",
  "Wednesday": "Rabu",
  "Thursday": "Kamis",
  "Friday": "Jumat",
  "Saturday": "Sabtu",
  "Sunday": "Minggu"
};

// Jadwal dari PHP → JavaScript
const eventSchedule = <?php echo json_encode($eventSchedule); ?>;

// Fungsi cari event berikutnya
function getNextEvent() {
  const now = new Date();
  let nearestEvent = null;
  let nearestTime = null;

  for (let i = 0; i < 7; i++) {
    let checkDay = new Date(now);
    checkDay.setDate(now.getDate() + i);
    const dayName = checkDay.toLocaleDateString('en-US', { weekday: 'long' });

    if (eventSchedule[dayName]) {
      const e = eventSchedule[dayName];
      const startTime = new Date(checkDay.getFullYear(), checkDay.getMonth(), checkDay.getDate(), e.startHour, e.startMinute);
      const endTime   = new Date(checkDay.getFullYear(), checkDay.getMonth(), checkDay.getDate(), e.endHour, e.endMinute);

      if (now < endTime) {
        nearestEvent = { day: dayName, start: startTime, end: endTime };
        nearestTime = (now < startTime) ? startTime : endTime;
        break;
      }
    }
  }
  return nearestEvent ? { ...nearestEvent, target: nearestTime } : null;
}

// Update countdown tiap detik
function updateCountdown() {
  const event = getNextEvent();
  if (!event) {
    document.getElementById("countdownPage").classList.remove("hidden");
    document.getElementById("eventPage").classList.add("hidden");
    document.getElementById("countdown").innerText = "Tidak ada event terjadwal.";
    return;
  }

  const now = new Date();
  if (now >= event.start && now < event.end) {
    // Event sedang berlangsung
    document.getElementById("countdownPage").classList.add("hidden");
    document.getElementById("eventPage").classList.remove("hidden");
  } else {
    // Countdown
    const distance = event.target - now;
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById("nextEventInfo").innerText =
      "Event berikutnya: " + dayMap[event.day] + " " +
      event.start.getHours().toString().padStart(2,'0') + ":" +
      event.start.getMinutes().toString().padStart(2,'0') +
      " - " + event.end.getHours().toString().padStart(2,'0') + ":" +
      event.end.getMinutes().toString().padStart(2,'0');

    document.getElementById("countdown").innerText =
      days + "h " + hours + "j " + minutes + "m " + seconds + "d ";
    
    document.getElementById("countdownPage").classList.remove("hidden");
    document.getElementById("eventPage").classList.add("hidden");
  }
}

setInterval(updateCountdown, 1000);
updateCountdown();

// Fitur Skip dengan password
function skipEvent() {
  const pass = prompt("Masukkan password untuk skip:");
  if (pass === "Rafkha40@") {
    alert("Password benar! Langsung masuk ke halaman event.");
    document.getElementById("countdownPage").classList.add("hidden");
    document.getElementById("eventPage").classList.remove("hidden");
  } else {
    alert("Password salah!");
  }
}
</script>
</body>
</html>
