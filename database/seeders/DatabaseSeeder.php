<?php
namespace Database\Seeders;
use App\Models\{Product,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  User::updateOrCreate(['staff_id'=>'ADMIN001'],['name'=>'Administrator Rexel','email'=>'admin@rexel.local','password'=>Hash::make('RexelAdmin123!'),'role'=>'admin','active'=>true]);
  User::updateOrCreate(['staff_id'=>'KRY001'],['name'=>'Karyawan Demo','email'=>'karyawan@rexel.local','password'=>Hash::make('RexelStaff123!'),'role'=>'employee','active'=>true]);
  $games=['PS3'=>["Grand Theft Auto V","The Last of Us","God of War III","God of War: Ascension","Uncharted: Drake's Fortune","Uncharted 2: Among Thieves","Uncharted 3: Drake's Deception","Red Dead Redemption","Red Dead Redemption: Undead Nightmare","Metal Gear Solid 4: Guns of the Patriots","Batman: Arkham City","Batman: Arkham Asylum","Batman: Arkham Origins","Resident Evil 5","Resident Evil 6","Resident Evil Revelations","Mortal Kombat","Tekken 6","Tekken Tag Tournament 2","Naruto Shippuden: Ultimate Ninja Storm 3","Naruto Shippuden: Ultimate Ninja Storm Revolution","PES 2018","FIFA 19","Need for Speed: Most Wanted","Gran Turismo 6"], 'PS4'=>["God of War (2018)","God of War Ragnarök","Marvel's Spider-Man","Marvel's Spider-Man: Miles Morales","The Last of Us Part II","Ghost of Tsushima","Red Dead Redemption 2","Grand Theft Auto V","Uncharted 4: A Thief's End","Uncharted: The Lost Legacy","Horizon Zero Dawn","Horizon Forbidden West","Days Gone","Bloodborne","Sekiro: Shadows Die Twice"]];
  foreach($games as $platform=>$titles)foreach($titles as $name)Product::firstOrCreate(['name'=>$name,'platform'=>$platform],['category'=>str_contains($name,'FIFA')||str_contains($name,'PES')?'Sports':(str_contains($name,'Resident Evil')?'Horror':'Action'),'description'=>'Game '.$platform.' pilihan Rexel Games. Lengkapi harga, stok, foto, dan deskripsi melalui dashboard.','price'=>0,'stock'=>0]);
  $accessories=[
   ['name'=>'DualShock 4 Wireless Controller','category'=>'Stik / Controller','description'=>'Controller nirkabel untuk PS4 dengan ergonomi nyaman dan fitur getar.'],
   ['name'=>'DualShock 3 Wireless Controller','category'=>'Stik / Controller','description'=>'Controller nirkabel untuk konsol PS3.'],
   ['name'=>'Kabel HDMI PS3/PS4','category'=>'Kabel','description'=>'Kabel HDMI untuk koneksi konsol ke televisi atau monitor.'],
   ['name'=>'Kabel USB Charging PS4','category'=>'Kabel','description'=>'Kabel data dan pengisian untuk controller DualShock 4.'],
   ['name'=>'Headset Gaming PS4','category'=>'Headset','description'=>'Headset gaming kompatibel dengan PS4 untuk obrolan dan audio.'],
   ['name'=>'Dock Charging DualShock','category'=>'Charger','description'=>'Dock pengisi daya untuk satu atau dua controller DualShock.'],
   ['name'=>'Kotak Penyimpanan Game PS','category'=>'Storage','description'=>'Kotak penyimpanan game dan aksesoris PS.'],
  ];
  foreach($accessories as $item)Product::firstOrCreate(['name'=>$item['name'],'platform'=>'Aksesoris'],['category'=>$item['category'],'description'=>$item['description'],'price'=>0,'stock'=>0]);
  $accessoriesExtended=[
   ['name'=>'Analog / Karet Stik PS3','category'=>'Sparepart Stik','description'=>'Karet atau tutup analog pengganti untuk stik PS3 DualShock 3.'],
   ['name'=>'Analog / Karet Stik PS4','category'=>'Sparepart Stik','description'=>'Karet atau tutup analog pengganti untuk stik PS4 DualShock 4.'],
   ['name'=>'Analog 3D Stik PS3','category'=>'Sparepart Stik','description'=>'Module analog 3D pengganti untuk stik PS3.'],
   ['name'=>'Analog 3D Stik PS4','category'=>'Sparepart Stik','description'=>'Module analog 3D pengganti untuk stik PS4.'],
   ['name'=>'Thumb Grip Stik PS3/PS4','category'=>'Pelindung Stik','description'=>'Pelindung silikon pada analog stik PS3 dan PS4.'],
   ['name'=>'Pelindung Silikon Controller PS4','category'=>'Pelindung','description'=>'Sarung silikon anti-selip untuk DualShock 4.'],
   ['name'=>'Pelindung Silikon Controller PS3','category'=>'Pelindung','description'=>'Sarung silikon anti-selip untuk DualShock 3.'],
   ['name'=>'Baterai Controller PS4','category'=>'Baterai','description'=>'Baterai pengganti untuk controller PS4.'],
   ['name'=>'Baterai Controller PS3','category'=>'Baterai','description'=>'Baterai pengganti untuk controller PS3.'],
   ['name'=>'Port Charging Controller PS4','category'=>'Sparepart Stik','description'=>'Port charging pengganti untuk DualShock 4.'],
   ['name'=>'Tombol L1/R1 Stik PS4','category'=>'Sparepart Stik','description'=>'Komponen tombol bahu pengganti untuk controller PS4.'],
   ['name'=>'Tombol X/O/Segitiga/Kotak Stik PS4','category'=>'Sparepart Stik','description'=>'Set karet atau tombol aksi untuk DualShock 4.'],
   ['name'=>'Kabel Power PS4','category'=>'Kabel','description'=>'Kabel daya untuk konsol PS4.'],
   ['name'=>'Memory Card PS3','category'=>'Storage','description'=>'Media penyimpanan data game untuk PS3.'],
   ['name'=>'Cooling Fan PS4','category'=>'Pendingin','description'=>'Kipas pendingin tambahan untuk konsol PS4.'],
  ];
  foreach($accessoriesExtended as $item)Product::firstOrCreate(['name'=>$item['name'],'platform'=>'Aksesoris'],['category'=>$item['category'],'description'=>$item['description'],'price'=>0,'stock'=>0]);
  $services=[
   ['name'=>'Jasa Benarkan PS3','category'=>'Repair Konsol','description'=>'Pemeriksaan dan perbaikan kerusakan umum konsol PS3.'],
   ['name'=>'Jasa Benarkan PS4','category'=>'Repair Konsol','description'=>'Pemeriksaan dan perbaikan kerusakan umum konsol PS4.'],
   ['name'=>'Jasa Benarkan Stik PS3','category'=>'Repair Stik','description'=>'Perbaikan stik PS3, termasuk drift, tombol, analog, dan port charging.'],
   ['name'=>'Jasa Benarkan Stik PS4','category'=>'Repair Stik','description'=>'Perbaikan stik PS4, termasuk drift, tombol, analog, dan port charging.'],
  ];
  foreach($services as $item)Product::updateOrCreate(['name'=>$item['name'],'platform'=>'Jasa'],['category'=>$item['category'],'description'=>$item['description'],'price'=>0,'stock'=>2147483647]);
 }
}
