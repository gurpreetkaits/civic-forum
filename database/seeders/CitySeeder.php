<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Build a code => id lookup from the states table
        $stateIds = DB::table('states')->pluck('id', 'code');

        $citiesByState = [
            'MH' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Amravati', 'Navi Mumbai'],
            'DL' => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Central Delhi', 'Dwarka', 'Rohini'],
            'KA' => ['Bengaluru', 'Mysuru', 'Hubli-Dharwad', 'Mangaluru', 'Belagavi', 'Kalaburagi', 'Davanagere', 'Ballari', 'Shivamogga', 'Tumakuru'],
            'TN' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Erode', 'Vellore', 'Thoothukudi', 'Thanjavur'],
            'UP' => ['Lucknow', 'Kanpur', 'Agra', 'Varanasi', 'Meerut', 'Prayagraj', 'Ghaziabad', 'Noida', 'Bareilly', 'Aligarh'],
            'GJ' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhinagar', 'Anand', 'Morbi'],
            'RJ' => ['Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer', 'Udaipur', 'Bhilwara', 'Alwar', 'Sikar', 'Sri Ganganagar'],
            'WB' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Bardhaman', 'Malda', 'Baharampur', 'Habra', 'Kharagpur'],
            'AP' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Rajahmundry', 'Tirupati', 'Kakinada', 'Kadapa', 'Anantapur'],
            'TS' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Khammam', 'Ramagundam', 'Mahbubnagar', 'Nalgonda', 'Adilabad', 'Suryapet'],
            'KL' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Kannur', 'Kottayam', 'Malappuram'],
            'MP' => ['Bhopal', 'Indore', 'Jabalpur', 'Gwalior', 'Ujjain', 'Sagar', 'Dewas', 'Satna', 'Ratlam', 'Rewa'],
            'BR' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia', 'Darbhanga', 'Bihar Sharif', 'Arrah', 'Begusarai', 'Katihar'],
            'PB' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Pathankot', 'Hoshiarpur', 'Batala', 'Moga'],
            'HR' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Karnal', 'Hisar', 'Rohtak', 'Sonipat', 'Yamunanagar', 'Panchkula'],
            'CG' => ['Raipur', 'Bhilai', 'Bilaspur', 'Korba', 'Durg', 'Rajnandgaon', 'Jagdalpur', 'Ambikapur'],
            'JH' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro', 'Hazaribagh', 'Deoghar', 'Giridih', 'Ramgarh'],
            'OD' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Puri', 'Balasore', 'Baripada'],
            'AS' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon'],
            'HP' => ['Shimla', 'Manali', 'Dharamshala', 'Solan', 'Mandi', 'Kullu', 'Hamirpur', 'Una'],
            'UK' => ['Dehradun', 'Haridwar', 'Rishikesh', 'Haldwani', 'Roorkee', 'Kashipur', 'Rudrapur', 'Nainital'],
            'GA' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda'],
            'TR' => ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailasahar', 'Ambassa'],
            'MN' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Kakching'],
            'ML' => ['Shillong', 'Tura', 'Jowai', 'Nongstoin', 'Williamnagar'],
            'MZ' => ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip', 'Kolasib'],
            'NL' => ['Kohima', 'Dimapur', 'Mokokchung', 'Tuensang', 'Wokha'],
            'AR' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tawang', 'Ziro'],
            'SK' => ['Gangtok', 'Namchi', 'Gyalshing', 'Mangan', 'Rangpo'],
            'JK' => ['Srinagar', 'Jammu', 'Anantnag', 'Baramulla', 'Sopore', 'Kathua', 'Udhampur'],
            'LA' => ['Leh', 'Kargil'],
            'CH' => ['Chandigarh'],
            'AN' => ['Port Blair', 'Bamboo Flat', 'Garacharma'],
            'DN' => ['Silvassa', 'Daman', 'Diu'],
            'LD' => ['Kavaratti', 'Agatti', 'Minicoy'],
            'PY' => ['Puducherry', 'Karaikal', 'Mahe', 'Yanam'],
        ];

        $rows = [];
        foreach ($citiesByState as $stateCode => $cities) {
            $stateId = $stateIds[$stateCode] ?? null;
            if (!$stateId) {
                continue;
            }

            foreach ($cities as $cityName) {
                $rows[] = [
                    'name' => $cityName,
                    'state_id' => $stateId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert in chunks to avoid packet size issues
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('cities')->insert($chunk);
        }
    }
}
