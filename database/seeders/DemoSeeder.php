<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Lookup maps
        $stateIds = DB::table('states')->pluck('id', 'code');
        $cityIds = DB::table('cities')->get()->mapWithKeys(fn ($c) => ["{$c->state_id}:{$c->name}" => $c->id]);
        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        // Helper to resolve city ID
        $cityId = function (string $stateCode, string $cityName) use ($stateIds, $cityIds) {
            $stateId = $stateIds[$stateCode];
            return $cityIds["{$stateId}:{$cityName}"] ?? null;
        };

        // ── Users ──────────────────────────────────────────────────
        $password = Hash::make('password');

        $usersData = [
            ['name' => 'Priya Sharma', 'username' => 'priya.sharma', 'email' => 'priya@example.com', 'bio' => 'Civic activist based in Delhi. Passionate about clean governance and women\'s safety.', 'state' => 'DL', 'city' => 'New Delhi', 'reputation' => 342],
            ['name' => 'Rahul Deshmukh', 'username' => 'rahul.deshmukh', 'email' => 'rahul@example.com', 'bio' => 'Software engineer and Mumbai local train commuter. Reporting infrastructure issues since 2020.', 'state' => 'MH', 'city' => 'Mumbai', 'reputation' => 518],
            ['name' => 'Ananya Iyer', 'username' => 'ananya.iyer', 'email' => 'ananya@example.com', 'bio' => 'Environmental researcher at IIT Madras. Focused on water and sanitation issues in Tamil Nadu.', 'state' => 'TN', 'city' => 'Chennai', 'reputation' => 276],
            ['name' => 'Vikram Singh', 'username' => 'vikram.singh', 'email' => 'vikram@example.com', 'bio' => 'Retired IAS officer. Advocating for transparent governance in Rajasthan.', 'state' => 'RJ', 'city' => 'Jaipur', 'reputation' => 891],
            ['name' => 'Meera Krishnan', 'username' => 'meera.krishnan', 'email' => 'meera@example.com', 'bio' => 'Teacher and education activist from Kerala. Fighting for better government school infrastructure.', 'state' => 'KL', 'city' => 'Kochi', 'reputation' => 167],
            ['name' => 'Arjun Patel', 'username' => 'arjun.patel', 'email' => 'arjun@example.com', 'bio' => 'Urban planner working on Ahmedabad\'s smart city initiatives. Interested in sustainable development.', 'state' => 'GJ', 'city' => 'Ahmedabad', 'reputation' => 445],
            ['name' => 'Fatima Begum', 'username' => 'fatima.begum', 'email' => 'fatima@example.com', 'bio' => 'Healthcare worker in rural Bihar. Documenting PHC conditions and healthcare access gaps.', 'state' => 'BR', 'city' => 'Patna', 'reputation' => 203],
            ['name' => 'Suresh Reddy', 'username' => 'suresh.reddy', 'email' => 'suresh@example.com', 'bio' => 'Auto-rickshaw driver and community organizer in Hyderabad. Voice for transport workers\' rights.', 'state' => 'TS', 'city' => 'Hyderabad', 'reputation' => 134],
            ['name' => 'Kavita Joshi', 'username' => 'kavita.joshi', 'email' => 'kavita@example.com', 'bio' => 'Journalist covering corruption and governance issues in Uttar Pradesh.', 'state' => 'UP', 'city' => 'Lucknow', 'reputation' => 612],
            ['name' => 'Deepak Nair', 'username' => 'deepak.nair', 'email' => 'deepak@example.com', 'bio' => 'IT professional in Bengaluru. Documenting traffic and infrastructure chaos in the tech capital.', 'state' => 'KA', 'city' => 'Bengaluru', 'reputation' => 389],
        ];

        $userRows = [];
        foreach ($usersData as $u) {
            $userRows[] = [
                'name' => $u['name'],
                'username' => $u['username'],
                'email' => $u['email'],
                'email_verified_at' => $now,
                'password' => $password,
                'bio' => $u['bio'],
                'state_id' => $stateIds[$u['state']],
                'city_id' => $cityId($u['state'], $u['city']),
                'reputation' => $u['reputation'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('users')->insert($userRows);
        $userIds = DB::table('users')->pluck('id', 'username');

        // ── Tags ───────────────────────────────────────────────────
        $tagsData = [
            'pothole', 'water-crisis', 'bmc', 'corruption', 'metro', 'pollution',
            'traffic', 'healthcare', 'education', 'womens-safety', 'ration-card',
            'rto', 'yamuna', 'stubble-burning', 'mid-day-meal', 'sewage',
            'flyover', 'bus-delay', 'government-hospital', 'teacher-shortage',
            'drinking-water', 'smart-city', 'farmer', 'electricity', 'housing',
        ];

        $tagRows = [];
        foreach ($tagsData as $tag) {
            $tagRows[] = [
                'name' => Str::title(str_replace('-', ' ', $tag)),
                'slug' => $tag,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('tags')->insert($tagRows);
        $tagIds = DB::table('tags')->pluck('id', 'slug');

        // ── Posts ──────────────────────────────────────────────────
        $postsData = $this->getPostsData();

        $postRows = [];
        $postTagMap = []; // index => [tag slugs]
        $postImageMap = []; // index => [label strings]

        foreach ($postsData as $i => $p) {
            $slug = Str::slug($p['title']);
            // Ensure unique slug
            $existing = DB::table('posts')->where('slug', $slug)->exists();
            if ($existing) {
                $slug .= '-' . ($i + 1);
            }

            $daysAgo = $p['days_ago'] ?? rand(1, 30);
            $publishedAt = now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $postRows[] = [
                'user_id' => $userIds[$p['user']],
                'category_id' => $categoryIds[$p['category']],
                'state_id' => $stateIds[$p['state']],
                'city_id' => isset($p['city']) ? $cityId($p['state'], $p['city']) : null,
                'title' => $p['title'],
                'slug' => $slug,
                'body' => $p['body'],
                'status' => 'published',
                'vote_count' => 0, // will be computed
                'comment_count' => 0, // will be computed
                'view_count' => rand(50, 2000),
                'published_at' => $publishedAt,
                'created_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ];

            $postTagMap[$i] = $p['tags'] ?? [];
            $postImageMap[$i] = $p['images'] ?? [];
        }

        // Insert posts one-by-one to avoid bulk slug conflicts
        $postIdsByIndex = [];
        foreach ($postRows as $i => $row) {
            DB::table('posts')->insert($row);
            $postIdsByIndex[$i] = DB::table('posts')->where('slug', $row['slug'])->value('id');
        }

        // ── Post-Tag pivot ─────────────────────────────────────────
        $pivotRows = [];
        foreach ($postTagMap as $i => $tags) {
            foreach ($tags as $tagSlug) {
                if (isset($tagIds[$tagSlug]) && isset($postIdsByIndex[$i])) {
                    $pivotRows[] = [
                        'post_id' => $postIdsByIndex[$i],
                        'tag_id' => $tagIds[$tagSlug],
                    ];
                }
            }
        }
        if ($pivotRows) {
            DB::table('post_tag')->insert($pivotRows);
        }

        // ── Placeholder images ─────────────────────────────────────
        $this->generatePlaceholderImages($postIdsByIndex, $postImageMap);

        // ── Comments ───────────────────────────────────────────────
        $commentsData = $this->getCommentsData();

        // Insert comments and track IDs for nesting
        $commentIdsByKey = []; // "postIndex:commentIndex" => id
        foreach ($commentsData as $c) {
            $postId = $postIdsByIndex[$c['post_index']] ?? null;
            if (!$postId) continue;

            $parentId = null;
            $depth = 0;
            if (isset($c['parent_key'])) {
                $parentId = $commentIdsByKey[$c['parent_key']] ?? null;
                $depth = $c['depth'] ?? 1;
            }

            $daysAgo = $c['days_ago'] ?? rand(0, 15);
            $createdAt = now()->subDays($daysAgo)->subHours(rand(0, 23));

            $commentId = DB::table('comments')->insertGetId([
                'post_id' => $postId,
                'user_id' => $userIds[$c['user']],
                'parent_id' => $parentId,
                'body' => $c['body'],
                'vote_count' => 0,
                'depth' => $depth,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if (isset($c['key'])) {
                $commentIdsByKey[$c['key']] = $commentId;
            }
        }

        // ── Votes ──────────────────────────────────────────────────
        $allUserIds = array_values($userIds->toArray());
        $allPostIds = array_values($postIdsByIndex);
        $allCommentIds = DB::table('comments')->pluck('id')->toArray();

        // Vote on posts
        $postVotes = [];
        foreach ($allPostIds as $postId) {
            // Each post gets 3-8 random votes
            $voters = collect($allUserIds)->shuffle()->take(rand(3, 8));
            foreach ($voters as $voterId) {
                $postVotes[] = [
                    'user_id' => $voterId,
                    'votable_id' => $postId,
                    'votable_type' => 'App\\Models\\Post',
                    'value' => rand(1, 100) <= 80 ? 1 : -1, // 80% upvotes
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($postVotes, 50) as $chunk) {
            DB::table('votes')->insert($chunk);
        }

        // Vote on comments
        $commentVotes = [];
        foreach ($allCommentIds as $commentId) {
            $voters = collect($allUserIds)->shuffle()->take(rand(1, 5));
            foreach ($voters as $voterId) {
                $commentVotes[] = [
                    'user_id' => $voterId,
                    'votable_id' => $commentId,
                    'votable_type' => 'App\\Models\\Comment',
                    'value' => rand(1, 100) <= 75 ? 1 : -1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($commentVotes, 50) as $chunk) {
            DB::table('votes')->insert($chunk);
        }

        // ── Recompute counts ───────────────────────────────────────
        // Post vote counts
        $postVoteCounts = DB::table('votes')
            ->where('votable_type', 'App\\Models\\Post')
            ->selectRaw('votable_id, SUM(value) as total')
            ->groupBy('votable_id')
            ->pluck('total', 'votable_id');

        foreach ($postVoteCounts as $id => $count) {
            DB::table('posts')->where('id', $id)->update(['vote_count' => $count]);
        }

        // Post comment counts
        $commentCounts = DB::table('comments')
            ->selectRaw('post_id, COUNT(*) as total')
            ->groupBy('post_id')
            ->pluck('total', 'post_id');

        foreach ($commentCounts as $postId => $count) {
            DB::table('posts')->where('id', $postId)->update(['comment_count' => $count]);
        }

        // Comment vote counts
        $commentVoteCounts = DB::table('votes')
            ->where('votable_type', 'App\\Models\\Comment')
            ->selectRaw('votable_id, SUM(value) as total')
            ->groupBy('votable_id')
            ->pluck('total', 'votable_id');

        foreach ($commentVoteCounts as $id => $count) {
            DB::table('comments')->where('id', $id)->update(['vote_count' => $count]);
        }
    }

    private function getPostsData(): array
    {
        return [
            // 0: Infrastructure - Mumbai potholes
            [
                'title' => 'Massive potholes on Western Express Highway causing daily accidents',
                'body' => "The stretch of Western Express Highway between Andheri and Goregaon has become a death trap for commuters. Over the past two weeks, I've counted at least 15 major potholes, some deep enough to swallow a motorcycle wheel entirely. Two accidents happened just this Monday — a delivery rider broke his collarbone and a family on a scooter skidded into oncoming traffic.\n\nBMC claimed they completed road repairs before the monsoon, but clearly the patchwork didn't survive the first heavy rain. The same spots that were \"repaired\" in June have now opened up worse than before. This is taxpayer money being literally washed away every monsoon season.\n\nI've filed complaints on the BMC portal (complaint numbers attached) and tweeted at the municipal commissioner, but zero response so far. Fellow Mumbaikars, please add your experiences and let's build pressure. We deserve roads that last more than one season.\n\nPhotos of the worst potholes attached. GPS coordinates: 19.1364° N, 72.8296° E near Oshiwara bridge.",
                'user' => 'rahul.deshmukh',
                'category' => 'infrastructure',
                'state' => 'MH',
                'city' => 'Mumbai',
                'tags' => ['pothole', 'bmc'],
                'days_ago' => 2,
                'images' => ['Pothole on Western Express Highway', 'Road damage near Oshiwara Bridge', 'BMC complaint screenshot'],
            ],
            // 1: Water - Chennai shortage
            [
                'title' => 'Adyar residents face 10-day water supply gap — tanker mafia thriving',
                'body' => "For the third time this year, our neighbourhood in Adyar has gone without municipal water supply for over 10 days. Metro Water's tanker schedule has completely collapsed. Meanwhile, private tanker operators are charging Rs 2,500 for a single load that cost Rs 800 just six months ago.\n\nElderly residents in our apartment complex are the worst affected — they can't carry water from ground floor tanks, and the building's overhead tank has been empty since Tuesday. Several families are now buying 20-litre cans from local shops at Rs 50 each, which adds up to Rs 3,000-4,000 per month on top of their water tax.\n\nThe irony is that Chennai received above-average rainfall this year, but the reservoirs are poorly maintained and distribution infrastructure is decades old. We need a long-term solution, not just crisis management every summer.\n\nI'm organizing a residents' meeting this Saturday to draft a formal petition to the Corporation. Anyone from Adyar, Besant Nagar, or Thiruvanmiyur facing similar issues — please join us.",
                'user' => 'ananya.iyer',
                'category' => 'water-sanitation',
                'state' => 'TN',
                'city' => 'Chennai',
                'tags' => ['water-crisis', 'drinking-water'],
                'days_ago' => 5,
                'images' => ['Empty overhead tank at apartment', 'Private tanker price board'],
            ],
            // 2: Healthcare - Bihar PHC
            [
                'title' => 'Only one doctor for 30,000 people — Samastipur PHC barely functional',
                'body' => "I recently visited the Primary Health Centre in a block of Samastipur district while documenting healthcare access in rural Bihar. What I found was heartbreaking but unfortunately not surprising — a single doctor serving a catchment area of approximately 30,000 people, with no specialist, no lab technician, and medicines that expired months ago.\n\nThe PHC building itself is in disrepair: broken windows, no functioning toilet for patients, and an examination room that doubles as a storage closet. The lone doctor, who requested anonymity, told me he hasn't received his salary for three months and is considering leaving.\n\nPregnant women from surrounding villages have to travel 45 km to the district hospital for basic check-ups. Two maternal deaths were reported in the last quarter — both women who couldn't make it to the hospital in time. The ambulance service (108) takes over an hour to reach this area.\n\nThis is a violation of the IPHS (Indian Public Health Standards) which mandate at least two doctors per PHC. I'm compiling data from 15 PHCs in the district and will be filing an RTI with the State Health Department.",
                'user' => 'fatima.begum',
                'category' => 'healthcare',
                'state' => 'BR',
                'city' => 'Patna',
                'tags' => ['healthcare', 'government-hospital'],
                'days_ago' => 8,
            ],
            // 3: Transportation - Bengaluru traffic
            [
                'title' => 'Silk Board junction: 45 minutes to cross 2 km — when will the flyover finish?',
                'body' => "Silk Board junction remains Bengaluru's worst traffic nightmare. My daily commute from HSR Layout to Koramangala — a distance of barely 2 kilometres — takes 45 minutes during peak hours. The elevated corridor project that was supposed to fix this has been \"under construction\" for over three years now, and the construction itself has made things worse.\n\nBMRCL's metro extension through this corridor was originally promised for 2024 completion. We're now told 2026, but looking at the pace of work, even that seems optimistic. Meanwhile, BBMP has dug up half the roads for white-topping, so the remaining lanes are reduced to one.\n\nI've calculated that Bengaluru commuters collectively lose approximately 2.5 crore man-hours per month stuck in traffic at just this one junction. That's an economic loss the city simply cannot afford if it wants to retain its position as India's tech capital.\n\nCan we get an official update on the metro timeline? And why isn't there a dedicated bus lane from Electronic City to Marathahalli?",
                'user' => 'deepak.nair',
                'category' => 'transportation',
                'state' => 'KA',
                'city' => 'Bengaluru',
                'tags' => ['traffic', 'metro', 'flyover'],
                'days_ago' => 3,
                'images' => ['Silk Board traffic jam at 9 AM', 'Stalled flyover construction', 'Metro pillar blocking road'],
            ],
            // 4: Corruption - RTO bribery
            [
                'title' => 'RTO office in Lucknow demands Rs 5,000 bribe for driving licence renewal',
                'body' => "I went to the Lucknow RTO office on Kanpur Road for a straightforward driving licence renewal. My documents were complete, my old licence was valid, and I had booked an online appointment. Despite all this, I was told my \"file would take 2-3 months\" unless I paid a \"processing fee\" to an agent sitting right outside the RTO office.\n\nThe agent, who appeared to work hand-in-glove with the counter staff, quoted Rs 5,000 for \"same-day processing.\" When I refused and insisted on going through the official channel, my application was mysteriously \"missing\" when I returned the next day. I had to start the process from scratch.\n\nOther applicants I spoke to confirmed this is standard practice — everyone pays the agent or waits indefinitely. One truck driver told me he paid Rs 8,000 for his commercial licence renewal because \"you can't afford to wait when your livelihood depends on it.\"\n\nI've filed a complaint on the anti-corruption helpline and also submitted a written complaint to the Transport Commissioner. Attaching photos of the agent operating openly outside the office.",
                'user' => 'kavita.joshi',
                'category' => 'corruption',
                'state' => 'UP',
                'city' => 'Lucknow',
                'tags' => ['corruption', 'rto'],
                'days_ago' => 6,
                'images' => ['Agents outside RTO office', 'Rate card being shown to applicants'],
            ],
            // 5: Environment - Yamuna pollution
            [
                'title' => 'Yamuna at Wazirabad smells like sewage — toxic foam spotted again',
                'body' => "The Yamuna river near Wazirabad barrage is once again covered in thick toxic foam. This morning, the foam was so dense that it spilled over onto the road alongside the river. The stench is unbearable for residents living within a kilometre radius.\n\nDespite Rs 7,000 crore spent on the Yamuna Action Plan over three decades, the river remains essentially a sewage drain within Delhi. The DO (Dissolved Oxygen) levels at Wazirabad are near zero — meaning nothing can survive in this water. Yet downstream, farmers in UP use this same water for irrigation, and the contamination enters our food chain.\n\nThe Chhath Puja ghats are just weeks away, and thousands of devotees will be standing in this toxic water. Every year we see the same headlines, the same outrage, and the same inaction. The DJB, DDA, and MoEFCC keep pointing fingers at each other while the river dies.\n\nI'm a civil engineer by training and I can tell you: the technology to treat Delhi's sewage exists and is affordable. What's missing is political will and inter-agency coordination. We need a single empowered authority for Yamuna rejuvenation.",
                'user' => 'priya.sharma',
                'category' => 'environment',
                'state' => 'DL',
                'city' => 'New Delhi',
                'tags' => ['yamuna', 'pollution', 'sewage'],
                'days_ago' => 1,
            ],
            // 6: Education - Teacher shortage
            [
                'title' => 'Government school in Malappuram has 800 students but only 6 teachers',
                'body' => "The Government Higher Secondary School in Malappuram district is running with just 6 teachers for over 800 students across classes 8 through 12. Three teacher positions have been vacant for over two years, and two more teachers are on long-term medical leave with no substitutes arranged.\n\nAs a result, students in Class 10 — the board exam year — have received zero instruction in Mathematics and Science for the past four months. The existing teachers are heroically trying to cover multiple subjects, but a Hindi teacher cannot be expected to teach Physics.\n\nParents who can afford it are sending their children to private tuitions, spending Rs 3,000-5,000 per month that many families here simply cannot afford. The rest of the students are left to self-study from textbooks alone. This two-tier system means government school students are already at a massive disadvantage before they even sit for exams.\n\nThe PTA has written to the District Education Officer three times. The response each time: \"recruitment process is underway.\" Meanwhile, an entire generation is losing out on quality education.",
                'user' => 'meera.krishnan',
                'category' => 'education',
                'state' => 'KL',
                'city' => 'Malappuram',
                'tags' => ['education', 'teacher-shortage'],
                'days_ago' => 10,
            ],
            // 7: Law & Order - Eve teasing
            [
                'title' => 'Repeated eve-teasing complaints near Jaipur station ignored by police',
                'body' => "Women commuters and college students using the area around Jaipur Junction railway station have been filing complaints about persistent harassment for months now. Groups of men loiter near the station exits, pass comments, follow women to bus stops, and even attempt to grab them in crowded areas.\n\nMy daughter, a college student, was followed from the station to her bus stop three times in one week. When we went to the nearest police chowki to file a complaint, the constable on duty said, \"Madam, aap time pe ghar jaaya karo\" (go home on time). He refused to file an FIR and instead suggested she \"change her route.\"\n\nThis is not an isolated incident — at least 12 women from our colony have similar experiences. The college has written to the police requesting increased patrolling and CCTV installation, but no action has been taken. The Women's Commission helpline takes the complaint but there's no follow-up.\n\nWe are planning a signature campaign and will approach the District Collector if the police continue to ignore us. Women have every right to use public spaces safely at any hour.",
                'user' => 'vikram.singh',
                'category' => 'womens-safety',
                'state' => 'RJ',
                'city' => 'Jaipur',
                'tags' => ['womens-safety'],
                'days_ago' => 4,
            ],
            // 8: Environment - Stubble burning
            [
                'title' => 'Stubble burning in Patiala — smoke blankets entire district despite ban',
                'body' => "Despite the NGT ban and government subsidies for crop residue management machines, stubble burning is in full swing across Patiala district. From my rooftop, I can count at least 8 fires burning in fields within a 5 km radius right now. The AQI in our area crossed 450 yesterday — classified as \"severe plus\" or emergency levels.\n\nSchools have been shut, my elderly father with COPD is struggling to breathe even indoors, and the local hospital's OPD is overflowing with respiratory complaints. This happens every year like clockwork, and every year the administration claims they've \"reduced burning by 30%.\"\n\nThe truth is that small and marginal farmers have no practical alternative. The Happy Seeder machine costs Rs 1.5 lakh, far beyond their means. The subsidy process is so complicated that most farmers give up. And the window between rice harvest and wheat sowing is barely 15-20 days — farmers simply cannot afford to wait.\n\nWe need a realistic solution: either free machine access through farmer cooperatives, or a government buyback scheme for crop residue. Fining poor farmers while offering no real alternative is not a solution.",
                'user' => 'vikram.singh',
                'category' => 'environment',
                'state' => 'PB',
                'city' => 'Patiala',
                'tags' => ['stubble-burning', 'pollution', 'farmer'],
                'days_ago' => 7,
            ],
            // 9: Water - Varanasi open drains
            [
                'title' => 'Open drains overflow in Varanasi old city — children falling sick daily',
                'body' => "The old city area near Dashashwamedh Ghat in Varanasi has open drains that overflow every time it rains. Raw sewage mixes with rainwater and floods the narrow lanes where thousands of families live. Children play in this contaminated water because they have no other open space.\n\nThe local PHC reports a 300% increase in diarrhoea and skin infection cases among children under 5 during the monsoon months. Waterborne diseases like typhoid and hepatitis A are also significantly higher in these wards compared to the city average.\n\nVaranasi was declared a Smart City in 2016, and crores have been allocated for infrastructure improvement. Yet the basic sanitation infrastructure in the most historic and densely populated part of the city remains unchanged since independence. The ghats look beautiful for tourists; the lanes behind them are a public health emergency.\n\nI urge the Varanasi Municipal Corporation to prioritize covered drainage in the old city wards. This is not a cosmetic issue — it's literally killing children.",
                'user' => 'kavita.joshi',
                'category' => 'water-sanitation',
                'state' => 'UP',
                'city' => 'Varanasi',
                'tags' => ['sewage', 'smart-city', 'water-crisis'],
                'days_ago' => 12,
            ],
            // 10: Transportation - Delhi Metro
            [
                'title' => 'Blue Line metro dangerously overcrowded — someone will get crushed',
                'body' => "The Delhi Metro Blue Line between Rajiv Chowk and Dwarka has become dangerously overcrowded during peak hours (8-10 AM and 5-7 PM). Yesterday, a woman fainted inside the packed coach between Janakpuri West and Uttam Nagar, and it took 15 minutes to get her out because people literally could not move.\n\nDMRC keeps adding trains but the frequency is still insufficient for the passenger load. The Blue Line carries over 12 lakh passengers daily — nearly double its designed capacity. Platform management is non-existent; at Rajiv Chowk, the queue extends to the staircase, creating a stampede risk.\n\nI've been a daily metro commuter for 8 years and the situation has never been this bad. The recent fare hike makes it worse — people are paying more for a worse experience. Meanwhile, DMRC's air-conditioned offices probably look nothing like the reality inside their trains.\n\nSuggestions: increase Blue Line frequency to 2-minute intervals during peak hours, add more coaches, and deploy crowd management staff at major stations. Or accept that more lines are needed and fast-track Phase 4.",
                'user' => 'priya.sharma',
                'category' => 'transportation',
                'state' => 'DL',
                'city' => 'New Delhi',
                'tags' => ['metro', 'traffic'],
                'days_ago' => 9,
            ],
            // 11: Corruption - Ration card fraud
            [
                'title' => 'Ghost beneficiaries on ration card list — real families going hungry in Darbhanga',
                'body' => "An RTI query I filed with the Food & Civil Supplies Department revealed that the ration distribution list for Ward 14 in Darbhanga contains 340 names — but our door-to-door survey found only 210 actual families in the ward. That means 130 ration cards are being used by \"ghost beneficiaries\" while genuine families are told their quota is exhausted.\n\nThe fair price shop owner is clearly complicit — he distributes the ghost cards' quota to the black market. Rice meant for BPL families at Rs 3/kg is being sold at Rs 25/kg in the open market. Several families I interviewed said they've been told to \"come next month\" for three months running.\n\nI've submitted the RTI findings and our survey data to the District Magistrate's office. The block development officer acknowledged the problem but said \"it requires a state-level database cleanup\" — essentially passing the buck.\n\nThis is happening in one of India's poorest districts where child malnutrition rates are among the highest in the country. Every stolen kilogram of rice is food taken from a hungry child's plate.",
                'user' => 'fatima.begum',
                'category' => 'corruption',
                'state' => 'BR',
                'city' => 'Darbhanga',
                'tags' => ['corruption', 'ration-card'],
                'days_ago' => 15,
            ],
            // 12: Infrastructure - Bengaluru flyover
            [
                'title' => 'Unfinished Hebbal flyover ramp causes near-miss accidents every day',
                'body' => "The ramp connecting Hebbal flyover to the airport road has been \"under construction\" for over 18 months. The incomplete structure forces vehicles to merge abruptly into a single lane at the exit, with no proper signage and barely functional street lights.\n\nI've personally witnessed three near-miss accidents in the last month alone — vehicles swerving at the last second to avoid the barricades. A cab driver told me a fatal accident happened there two weeks ago but didn't make the news because \"accidents here are too common to report.\"\n\nBBMP's traffic engineering wing seems to have no concept of temporary traffic management during construction. There are no reflective barriers, no speed limit signs approaching the merge point, and the existing signage is hidden behind overgrown bushes. At night, this stretch is essentially invisible.\n\nThis flyover expansion was part of a Rs 500 crore package announced with much fanfare. Where is the accountability for the delay and the safety hazard it has created?",
                'user' => 'deepak.nair',
                'category' => 'infrastructure',
                'state' => 'KA',
                'city' => 'Bengaluru',
                'tags' => ['flyover', 'traffic'],
                'days_ago' => 11,
            ],
            // 13: Healthcare - Overcrowded hospital
            [
                'title' => 'Osmania General Hospital: patients lying on floor, 3 patients per bed',
                'body' => "A visit to Osmania General Hospital in Hyderabad reveals the crumbling state of public healthcare in Telangana. The 1,000-bed hospital routinely handles 1,800+ inpatients — meaning patients share beds, lie on the floor in corridors, and even spill out onto the veranda.\n\nThe emergency ward is the worst. Patients with fractures wait 6-8 hours for an X-ray because there's only one functioning machine. The ICU has 12 ventilators for a city of one crore people. The pharmacy window has a permanent sign: \"These medicines are not available\" with a list that includes basic antibiotics and painkillers.\n\nDoctors and nurses are doing their best under impossible conditions. One resident doctor told me she handles 80+ patients per shift with no break. The hospital hasn't hired new nursing staff in three years despite increasing patient load.\n\nThe Telangana government recently announced a Rs 1,200 crore \"super-specialty block\" for Osmania. But we don't need a shiny new building — we need functioning equipment, adequate staff, and basic medicines in the existing one.",
                'user' => 'suresh.reddy',
                'category' => 'healthcare',
                'state' => 'TS',
                'city' => 'Hyderabad',
                'tags' => ['healthcare', 'government-hospital'],
                'days_ago' => 14,
            ],
            // 14: Transportation - BMTC bus delays
            [
                'title' => 'BMTC bus route 500 — 40 minute wait, standing room only, rude conductors',
                'body' => "Route 500 (Kempegowda Bus Station to Electronic City) is supposed to run every 10 minutes during peak hours according to the BMTC schedule. The reality? You're lucky if a bus shows up within 40 minutes. And when it does, it's packed to 200% capacity with people hanging off the footboard.\n\nThis route serves thousands of IT professionals who choose public transport to reduce traffic congestion. But BMTC is actively punishing people for making the environmentally responsible choice. After waiting 40 minutes in Bengaluru's sun, you get to stand for another hour in a bus with no functional AC (despite it being an AC route) and a conductor who behaves like you're personally inconveniencing him.\n\nI tracked the actual frequency for one week using a spreadsheet: the average gap between buses was 28 minutes, with a maximum of 52 minutes. That's nearly unacceptable for a route that supposedly has 15 buses allocated.\n\nBMTC needs to either increase the fleet on this route or be honest about the schedule so people can plan accordingly. A real-time bus tracking app — like every other major city has — would also help enormously.",
                'user' => 'deepak.nair',
                'category' => 'transportation',
                'state' => 'KA',
                'city' => 'Bengaluru',
                'tags' => ['bus-delay', 'traffic'],
                'days_ago' => 6,
            ],
            // 15: Education - Mid-day meal
            [
                'title' => 'Insects found in mid-day meal at government school in Agra — 12 children hospitalised',
                'body' => "Twelve children from a government primary school in Agra were hospitalised yesterday after insects were found in the mid-day meal served to students. The affected children, aged 6-10, complained of stomach pain and vomiting within an hour of eating.\n\nThis is the third such incident in Agra district in the past six months. The mid-day meal is prepared by a local self-help group under contract with the education department. An inspection of the kitchen revealed appalling hygiene conditions: no proper storage for grains, cooking vessels visibly unclean, and no separate area for washing and preparation.\n\nThe mid-day meal scheme is a lifeline for millions of children from underprivileged backgrounds — for many, it's the only nutritious meal they get all day. But when the implementation is this negligent, the scheme designed to help children ends up harming them.\n\nWe demand: immediate suspension of the current meal provider, a thorough inspection of all mid-day meal kitchens in the district, and implementation of the food safety standards that exist on paper but are never enforced.",
                'user' => 'kavita.joshi',
                'category' => 'education',
                'state' => 'UP',
                'city' => 'Agra',
                'tags' => ['mid-day-meal', 'education'],
                'days_ago' => 3,
            ],
            // 16: Smart City - Ahmedabad
            [
                'title' => 'Ahmedabad Smart City project — crores spent but basic roads still unpaved in Vastral',
                'body' => "Ahmedabad was selected in the first round of the Smart City Mission in 2016. Eight years later, the Vastral area — home to over 2 lakh residents — still doesn't have properly paved internal roads. The \"smart\" part seems to apply only to the Sabarmati Riverfront and SG Highway corridor while peripheral areas remain neglected.\n\nThe Smart City proposal promised \"area-based development\" covering 50 sq km. But the actual investment has been concentrated in a 5 sq km area that was already relatively well-developed. Meanwhile, Vastral roads turn into mud tracks during monsoon, there's no covered drainage, and streetlights work only on the main road.\n\nAs an urban planner, I find this deeply ironic. Smart City should mean smart governance — using data and technology to serve all citizens equitably. Instead, it's become a branding exercise that deepens existing inequalities.\n\nI've compiled data comparing Smart City spending per capita across different zones of Ahmedabad. The disparity is shocking. Will share the full analysis once the RTI response comes through.",
                'user' => 'arjun.patel',
                'category' => 'digital-governance',
                'state' => 'GJ',
                'city' => 'Ahmedabad',
                'tags' => ['smart-city'],
                'days_ago' => 18,
            ],
            // 17: Energy - Power cuts
            [
                'title' => 'Daily 4-hour power cuts in rural Jharkhand — inverters not a solution for the poor',
                'body' => "Villages in Ramgarh district of Jharkhand face scheduled power cuts of 4 hours daily, but the actual outage often extends to 8-10 hours. The erratic supply damages appliances, and voltage fluctuation is severe enough that even stabilizers can't cope. Last week, three houses in our village had their TVs and refrigerators destroyed by a sudden voltage spike.\n\nThe standard advice of \"buy an inverter\" doesn't work for families earning Rs 5,000-8,000 per month. A basic inverter setup costs Rs 15,000-20,000 — that's three months of income. Solar panels under PM Surya Ghar are great in theory but the installation backlog in Jharkhand is over 18 months.\n\nThe real issue is the distribution infrastructure. The transformer serving our cluster of 200 households is rated for 63 KVA but the load has exceeded 100 KVA due to new connections under Saubhagya scheme. Nobody upgraded the transformer. The result: frequent burnouts, low voltage, and unplanned outages.\n\nJBVNL needs to survey and upgrade transformers before approving new connections. Giving people electricity connections without adequate infrastructure is just setting them up for disappointment.",
                'user' => 'fatima.begum',
                'category' => 'energy',
                'state' => 'JH',
                'city' => 'Ramgarh',
                'tags' => ['electricity'],
                'days_ago' => 20,
            ],
            // 18: Housing - Mumbai slum rehabilitation
            [
                'title' => 'Dharavi redevelopment promises — 15 years of waiting, families still in 80 sqft rooms',
                'body' => "The Dharavi Redevelopment Project has been in various stages of \"planning\" and \"tendering\" for over 15 years. Meanwhile, families in Dharavi continue to live in 80-100 square feet rooms — cooking, sleeping, and studying in the same cramped space. Shared toilets serve 50+ people, and during monsoon, water enters homes waist-deep.\n\nThe latest iteration of the project promises 350 sqft flats for eligible residents. But the eligibility criteria keep changing, and thousands of families who were surveyed in 2011 are now being told they need to prove residency again. The cut-off date for eligibility is a moving target that seems designed to exclude as many people as possible.\n\nDharavi isn't just a slum — it's a thriving economic ecosystem generating Rs 5,000 crore annually through leather, pottery, recycling, and garment industries. Any redevelopment that displaces these livelihoods without providing viable alternatives will destroy lives.\n\nWe need rehabilitation that respects the community's economic fabric, provides adequate housing (not token 350 sqft flats), and doesn't force people to relocate to distant suburbs where there's no work.",
                'user' => 'rahul.deshmukh',
                'category' => 'housing',
                'state' => 'MH',
                'city' => 'Mumbai',
                'tags' => ['housing'],
                'days_ago' => 22,
            ],
            // 19: Agriculture - Farmer issues
            [
                'title' => 'Onion farmers in Nashik get Rs 2/kg — storage facilities non-existent',
                'body' => "Onion farmers in Nashik district are getting Rs 2 per kg at the Lasalgaon APMC market — the largest onion market in Asia. The same onion reaches Mumbai consumers at Rs 40/kg. The middlemen and transport cartel pocket the entire margin while farmers can't even recover their input costs.\n\nThe fundamental problem is lack of cold storage. Onions need temperature-controlled storage to last beyond 2-3 weeks. Without it, farmers are forced to sell immediately after harvest when the market is flooded and prices crash. The government promised 500 cold storage units under the Agriculture Infrastructure Fund — Nashik has received exactly 3.\n\nI visited Shri Rajendra Patil's farm yesterday. He grew onions on 4 acres, invested Rs 1.8 lakh in seeds, fertilizer, labour, and irrigation. His total sale proceeds: Rs 48,000. He's now Rs 1.3 lakh in debt for one season alone. \"If I'd left the field empty, I'd be richer,\" he told me.\n\nFarmer suicides in Nashik district are at a 5-year high. The connection between market exploitation and farmer distress is obvious to everyone except policymakers.",
                'user' => 'rahul.deshmukh',
                'category' => 'agriculture',
                'state' => 'MH',
                'city' => 'Nashik',
                'tags' => ['farmer'],
                'days_ago' => 16,
            ],
            // 20: Law & Order - Traffic enforcement
            [
                'title' => 'Wrong-way driving on Hyderabad ORR goes unpunished — CCTV cameras are for show',
                'body' => "The Outer Ring Road in Hyderabad has become a free-for-all zone where traffic rules are optional. Wrong-way driving, especially at exits and service roads, is rampant. I've captured dashcam footage of vehicles driving against traffic on at least 8 occasions in the past month — including an RTC bus.\n\nThe ORR has CCTV cameras installed every 500 metres — I counted them. Yet challans for wrong-way driving are virtually non-existent. When I inquired at the Cyberabad Traffic Police office, they said the cameras are \"primarily for monitoring, not enforcement.\" Then what exactly are they for?\n\nTwo fatal head-on collisions on the ORR were reported last quarter, both involving wrong-way vehicles. These deaths were entirely preventable if basic traffic laws were enforced.\n\nProposed solutions: activate automated challan generation from existing CCTV, install physical barriers at wrong-way entry points (rubber bollards cost Rs 500 each), and station traffic police at the 5 worst exit ramps during peak hours.",
                'user' => 'suresh.reddy',
                'category' => 'law-order',
                'state' => 'TS',
                'city' => 'Hyderabad',
                'tags' => ['traffic'],
                'days_ago' => 13,
            ],
            // 21: Disability Rights - Accessibility
            [
                'title' => 'Not one government building in Kochi is truly wheelchair accessible',
                'body' => "As part of an accessibility audit conducted by our disability rights group, we surveyed 25 government offices in Kochi — including the Collectorate, Taluk Office, Sub-Registrar offices, and municipal corporation buildings. The finding: not a single building is fully wheelchair accessible as mandated by the Rights of Persons with Disabilities Act, 2016.\n\nSpecifically: 18 buildings have steps at the entrance with no ramp. Of the 7 that have ramps, 4 are too steep to use safely (exceeding the 1:12 gradient specified in the Act). Only 2 buildings have accessible toilets, but one was locked and used as storage. Zero buildings have tactile flooring for visually impaired visitors. Elevators exist in 3 buildings but were non-functional in 2.\n\nThis means that any person with a disability who needs to interact with the government — for property registration, driving licence, birth certificate, pension — must depend on someone to physically carry them up stairs. This is not just inconvenient; it's a violation of their fundamental rights and dignity.\n\nWe've submitted our audit report to the State Commissioner for Persons with Disabilities. The RPwD Act gives the government a deadline to make all public buildings accessible. That deadline passed in 2022.",
                'user' => 'meera.krishnan',
                'category' => 'disability-rights',
                'state' => 'KL',
                'city' => 'Kochi',
                'tags' => [],
                'days_ago' => 25,
            ],
            // 22: Employment - Job scam
            [
                'title' => 'Fake job placement agency in Surat cheats 200 young graduates of Rs 15,000 each',
                'body' => "A fake job placement agency operating from Ring Road in Surat has cheated approximately 200 young graduates out of Rs 15,000 each as \"registration and processing fees\" for non-existent jobs. The agency, operating under the name \"GlobalConnect HR Solutions,\" promised call centre and data entry positions in Ahmedabad and Mumbai at salaries of Rs 25,000-35,000 per month.\n\nVictims were given elaborate \"offer letters\" on fake company letterheads, asked to report for \"training\" that never happened, and when they tried to contact the agency for follow-up, the office was found shuttered. The total estimated fraud: Rs 30 lakh.\n\nMost victims are first-generation graduates from lower-middle-class families who spent their savings on the fee. Many had turned down other opportunities believing they had a confirmed job. One victim, a 22-year-old woman, told me her family borrowed the Rs 15,000 from a moneylender at 5% monthly interest.\n\nAn FIR has been filed at Varachha police station but the investigation is moving at a glacial pace. If anyone has information about the operators, please contact the police or DM me. These scammers prey on desperation — we need to spread awareness so others don't fall victim.",
                'user' => 'arjun.patel',
                'category' => 'employment',
                'state' => 'GJ',
                'city' => 'Surat',
                'tags' => [],
                'days_ago' => 19,
            ],
            // 23: Infrastructure - Road quality
            [
                'title' => 'NH-44 near Nagpur: newly built highway already cracking after first monsoon',
                'body' => "A section of NH-44 (the main north-south highway) near Nagpur that was completed just 8 months ago is already showing major cracks and surface disintegration. The 12 km stretch between Kamptee and Kanhan, built at a cost of Rs 180 crore, has developed potholes and the top bituminous layer is peeling off in sheets.\n\nThis is a national highway carrying heavy truck traffic between Delhi and Hyderabad. The road was supposed to be built to IRC (Indian Roads Congress) standards with a design life of 15 years. It hasn't survived its first monsoon. Either the material specifications were violated, or the construction supervision was non-existent — or both.\n\nThe contractor is reportedly the same firm that built another failed stretch near Wardha two years ago. Yet they continue to receive government contracts. The NHAI toll booth at Kanhan is operational and collecting Rs 175 per car — for a road that's already worse than what existed before.\n\nI'm filing an RTI to get the quality test reports, contractor details, and supervision records for this stretch. If the specifications were violated, criminal charges should be filed under the Prevention of Corruption Act.",
                'user' => 'rahul.deshmukh',
                'category' => 'infrastructure',
                'state' => 'MH',
                'city' => 'Nagpur',
                'tags' => ['pothole', 'corruption'],
                'days_ago' => 17,
            ],
            // 24: Water & Sanitation - Groundwater
            [
                'title' => 'Jaipur groundwater level dropping 3 feet per year — borewells running dry',
                'body' => "Groundwater levels in Jaipur have been declining at an alarming rate of 3 feet per year for the past decade. Areas like Mansarovar, Pratap Nagar, and Jagatpura that had water at 80 feet depth ten years ago now need borewells drilled to 250+ feet. And even at that depth, the yield is barely sufficient.\n\nThe cause is clear: unchecked urbanization, zero rainwater harvesting enforcement, and the death of traditional water bodies. Jaipur once had over 200 step-wells (baoris) and lakes. Most have been encroached upon or filled with garbage. The Amanishah Nala, which once recharged groundwater across South Jaipur, is now an open sewer.\n\nThe Rajasthan government made rainwater harvesting mandatory for all new constructions in 2016. But compliance is estimated at less than 15%. Nobody checks, nobody penalizes. Meanwhile, the government's own buildings — including the new Secretariat — don't have rainwater harvesting systems.\n\nAt this rate, Jaipur could become the first major Indian city to run out of groundwater. The Central Ground Water Board's own data supports this projection. We need emergency measures: strict enforcement of rainwater harvesting, revival of traditional water bodies, and a moratorium on new borewells in critical zones.",
                'user' => 'vikram.singh',
                'category' => 'water-sanitation',
                'state' => 'RJ',
                'city' => 'Jaipur',
                'tags' => ['water-crisis', 'drinking-water'],
                'days_ago' => 21,
            ],
        ];
    }

    private function getCommentsData(): array
    {
        return [
            // Post 0: Mumbai potholes (5 comments, 1 nested)
            ['post_index' => 0, 'user' => 'deepak.nair', 'body' => 'Same situation on the Eastern Express Highway near Thane. BMC seems to use the cheapest possible material for road repairs. The potholes reappear within weeks. Has anyone tried filing a PIL? Individual complaints clearly don\'t work.', 'days_ago' => 1, 'key' => '0:0'],
            ['post_index' => 0, 'user' => 'priya.sharma', 'body' => 'I\'ve documented similar issues in Delhi on the Ring Road stretch near Ashram. It seems like every Indian city has the same monsoon-pothole cycle. The fundamental issue is the tendering process — lowest bidder gets the contract regardless of quality track record.', 'days_ago' => 1, 'key' => '0:1'],
            ['post_index' => 0, 'user' => 'arjun.patel', 'body' => 'As an urban planner, I can tell you the problem is drainage beneath the road surface. Without proper sub-surface drainage, water seeps under the bitumen and weakens the base. International standards mandate a gravel drainage layer — most Indian roads skip this to cut costs.', 'days_ago' => 1, 'key' => '0:2'],
            ['post_index' => 0, 'user' => 'rahul.deshmukh', 'body' => '@arjun.patel That\'s really insightful. So even if they use quality bitumen on top, the road will still fail without proper drainage underneath? That explains why the same spots keep breaking every year.', 'days_ago' => 0, 'parent_key' => '0:2', 'depth' => 1, 'key' => '0:3'],
            ['post_index' => 0, 'user' => 'vikram.singh', 'body' => 'The Rajasthan PWD actually started using Geo-synthetic reinforcement on some national highways and the results are significantly better. The initial cost is 15% higher but the lifecycle cost is much lower. BMC should look into this.', 'days_ago' => 0, 'key' => '0:4'],

            // Post 1: Chennai water (4 comments, 1 nested)
            ['post_index' => 1, 'user' => 'meera.krishnan', 'body' => 'The situation in parts of Kochi is similar though not as severe. What I don\'t understand is why Indian cities can\'t implement rainwater harvesting at a meaningful scale. Singapore captures and reuses 70% of its rainfall. We let it all flow into the sea.', 'days_ago' => 4, 'key' => '1:0'],
            ['post_index' => 1, 'user' => 'arjun.patel', 'body' => 'Chennai\'s problem is unique because the city sits on a clay bed that doesn\'t allow natural groundwater recharge. This makes surface water management even more critical. The encroachment on Pallikaranai marshland has reduced natural water storage capacity by 90%.', 'days_ago' => 4, 'key' => '1:1'],
            ['post_index' => 1, 'user' => 'ananya.iyer', 'body' => 'Thank you for the support, everyone. Our Saturday meeting had 45 attendees. We\'ve drafted the petition and will submit it on Monday. Also connecting with other RWAs across Adyar for a coordinated approach.', 'days_ago' => 2, 'key' => '1:2'],
            ['post_index' => 1, 'user' => 'priya.sharma', 'body' => '@ananya.iyer That\'s great progress! If you need any help with the media outreach, I have contacts with a few national news desks. Water issues in metros always get attention.', 'days_ago' => 1, 'parent_key' => '1:2', 'depth' => 1, 'key' => '1:3'],

            // Post 2: Bihar PHC (3 comments)
            ['post_index' => 2, 'user' => 'kavita.joshi', 'body' => 'This is heartbreaking but not surprising. The National Health Mission data shows Bihar has the worst doctor-to-population ratio in the country — 1:28,000 against the WHO recommendation of 1:1,000. The RTI approach is good; please share the response when you get it.', 'days_ago' => 7, 'key' => '2:0'],
            ['post_index' => 2, 'user' => 'vikram.singh', 'body' => 'The problem isn\'t just recruitment — it\'s retention. Doctors posted to rural areas face terrible living conditions, no career growth, and sometimes even safety threats. We need to make rural postings attractive through housing, hardship allowance, and guaranteed transfers after 3 years.', 'days_ago' => 6, 'key' => '2:1'],
            ['post_index' => 2, 'user' => 'suresh.reddy', 'body' => 'In Telangana, the Basti Dawakhanas (neighbourhood clinics) model has worked reasonably well in urban areas. Each clinic handles 100-150 patients daily with one doctor and two support staff. Could something similar work in rural Bihar? Of course, the challenge is finding doctors willing to serve there.', 'days_ago' => 5, 'key' => '2:2'],

            // Post 3: Bengaluru traffic (4 comments, 2 nested)
            ['post_index' => 3, 'user' => 'rahul.deshmukh', 'body' => 'Bengaluru and Mumbai are competing for worst traffic in India. At least you guys are getting a metro — Mumbai\'s metro network is years behind schedule too. The Aarey metro car shed controversy delayed Metro 3 by almost 5 years.', 'days_ago' => 2, 'key' => '3:0'],
            ['post_index' => 3, 'user' => 'suresh.reddy', 'body' => 'Hyderabad had a similar problem at Biodiversity Junction. What helped was restricting U-turns and building grade separators. Silk Board needs a proper interchange, not just a flyover extension. But I know that requires land acquisition which is the real bottleneck.', 'days_ago' => 2, 'key' => '3:1'],
            ['post_index' => 3, 'user' => 'deepak.nair', 'body' => 'Update: I spoke to a BMRCL official (off the record). He confirmed the metro extension to Silk Board won\'t be operational before December 2027. The delay is due to utility shifting — BWSSB water mains under the road are taking months to relocate.', 'days_ago' => 1, 'key' => '3:2'],
            ['post_index' => 3, 'user' => 'arjun.patel', 'body' => '@deepak.nair December 2027? That\'s devastating. By then traffic will be even worse with all the new IT parks coming up in Bommasandra. Has anyone modelled what the traffic would look like by then without the metro?', 'days_ago' => 0, 'parent_key' => '3:2', 'depth' => 1, 'key' => '3:3'],

            // Post 4: RTO corruption (3 comments, 1 nested)
            ['post_index' => 4, 'user' => 'vikram.singh', 'body' => 'The Rajasthan RTO was similarly corrupt until they implemented 100% online processing for licence renewal in 2023. Now you submit documents online, get a slot for biometrics, and the licence is mailed to your address. Agent raj has reduced significantly, though not eliminated entirely.', 'days_ago' => 5, 'key' => '4:0'],
            ['post_index' => 4, 'user' => 'rahul.deshmukh', 'body' => 'Maharashtra implemented a similar online system and it\'s worked well in Mumbai at least. The key is removing discretionary power from the counter staff. If the system auto-approves based on document verification, there\'s nothing left to demand a bribe for.', 'days_ago' => 4, 'key' => '4:1'],
            ['post_index' => 4, 'user' => 'kavita.joshi', 'body' => '@vikram.singh That gives me hope. I\'ll include the Rajasthan model as a recommendation in my article about this. The UP transport department needs to see that other states have solved this problem.', 'days_ago' => 3, 'parent_key' => '4:0', 'depth' => 1, 'key' => '4:2'],

            // Post 5: Yamuna pollution (3 comments)
            ['post_index' => 5, 'user' => 'arjun.patel', 'body' => 'The Sabarmati Riverfront in Ahmedabad is often cited as a success story, but honestly the water quality is only marginally better. We just built nice walkways around a dirty river. The lesson: infrastructure alone doesn\'t clean rivers. You need sewage treatment capacity to match the sewage generation.', 'days_ago' => 0, 'key' => '5:0'],
            ['post_index' => 5, 'user' => 'ananya.iyer', 'body' => 'As an environmental researcher, I\'d add that the Yamuna\'s biggest problem is the 22 drains that discharge directly into the river within Delhi. If even the 5 largest drains are intercepted and treated, DO levels would improve dramatically. The technology exists — it\'s a governance problem.', 'days_ago' => 0, 'key' => '5:1'],
            ['post_index' => 5, 'user' => 'kavita.joshi', 'body' => 'I covered the Yamuna extensively during my time in Delhi. The most depressing fact: the river is technically \"dead\" (zero dissolved oxygen) for 22 km through Delhi, but it\'s declared \"clean\" by the time it reaches Agra because of dilution from tributaries. Our standards are literally \"dilution is the solution to pollution.\"', 'days_ago' => 0, 'key' => '5:2'],

            // Post 6: Teacher shortage (2 comments)
            ['post_index' => 6, 'user' => 'fatima.begum', 'body' => 'The situation in Bihar is even worse. Some government schools in remote areas have been running with a single teacher for all classes for years. The concept of \"single-teacher schools\" has been normalised, which is just another way of saying we\'ve given up on educating rural children.', 'days_ago' => 9, 'key' => '6:0'],
            ['post_index' => 6, 'user' => 'priya.sharma', 'body' => 'Delhi government schools showed that political will can transform public education. They invested in infrastructure, hired teachers, and school enrollment actually shifted from private to government schools. It\'s possible — other states just need to prioritise education over populist schemes.', 'days_ago' => 8, 'key' => '6:1'],

            // Post 7: Eve teasing (3 comments, 1 nested)
            ['post_index' => 7, 'user' => 'priya.sharma', 'body' => 'The police response is unfortunately typical. The \"go home early\" advice is victim-blaming disguised as concern. What we need is a zero-tolerance policy like the \"She Team\" initiative in Hyderabad — plainclothes women officers who catch harassers in the act.', 'days_ago' => 3, 'key' => '7:0'],
            ['post_index' => 7, 'user' => 'meera.krishnan', 'body' => 'I\'d suggest documenting each incident with video evidence and filing online FIRs through the state police portal. The advantage of online FIRs is that there\'s a digital trail — the police can\'t pretend the complaint doesn\'t exist.', 'days_ago' => 3, 'key' => '7:1'],
            ['post_index' => 7, 'user' => 'vikram.singh', 'body' => '@priya.sharma The She Team model is excellent. I\'ll bring this up at the next District Peace Committee meeting. The SP is generally receptive to suggestions if they come with examples from other states.', 'days_ago' => 2, 'parent_key' => '7:0', 'depth' => 1, 'key' => '7:2'],

            // Post 8: Stubble burning (2 comments)
            ['post_index' => 8, 'user' => 'priya.sharma', 'body' => 'Delhi literally cannot breathe every November because of this. And it\'s a problem that everyone knows how to solve but nobody wants to pay for. The cooperative model for Happy Seeder access is the most practical suggestion I\'ve heard. Even if the government provides machines at the block level, farmers can share them.', 'days_ago' => 6, 'key' => '8:0'],
            ['post_index' => 8, 'user' => 'arjun.patel', 'body' => 'Some states are experimenting with biochar production from crop residue — essentially converting the stubble into a useful soil amendment. It creates employment, improves soil quality, and eliminates burning. The initial results from Haryana pilot projects are promising.', 'days_ago' => 5, 'key' => '8:1'],

            // Post 9: Varanasi drains (3 comments)
            ['post_index' => 9, 'user' => 'fatima.begum', 'body' => 'The Smart City label has become a cruel joke in many Indian cities. In Patna, the Smart City project built a fancy \"command centre\" with giant screens while basic drainage in Kankarbagh remains non-existent. Priorities are completely inverted.', 'days_ago' => 11, 'key' => '9:0'],
            ['post_index' => 9, 'user' => 'ananya.iyer', 'body' => 'The children falling sick from contaminated water is the most urgent concern here. Can local NGOs distribute water purification tablets as an immediate intervention while the infrastructure fight continues? ORS packets should also be stocked at local shops during monsoon.', 'days_ago' => 10, 'key' => '9:1'],
            ['post_index' => 9, 'user' => 'vikram.singh', 'body' => 'Varanasi\'s old city presents a unique challenge because the lanes are too narrow for conventional sewer pipes. But small-bore sewer systems have been successfully used in similar settings in other countries. The technology exists — someone just needs to adapt it for Varanasi.', 'days_ago' => 9, 'key' => '9:2'],

            // Post 10: Delhi Metro (2 comments)
            ['post_index' => 10, 'user' => 'deepak.nair', 'body' => 'Bengaluru Metro is heading the same direction — the Phase 2 lines are going to be overcrowded from day one because the city has grown so much since the routes were planned. We need to build for the demand of 2035, not 2025. But that requires upfront investment that no government wants to make.', 'days_ago' => 8, 'key' => '10:0'],
            ['post_index' => 10, 'user' => 'suresh.reddy', 'body' => 'The Hyderabad Metro handles crowds better because they designed wider coaches and longer platforms from the start (they learned from Delhi\'s mistakes). The issue is that Delhi Metro was India\'s first — they didn\'t have a reference point. But DMRC should retrofit where possible.', 'days_ago' => 7, 'key' => '10:1'],

            // Post 11: Ration card fraud (3 comments, 1 nested)
            ['post_index' => 11, 'user' => 'kavita.joshi', 'body' => 'This is exactly the kind of investigative work that can make a real difference. 130 ghost beneficiaries in one ward suggests the problem is systematic, not just a few fake cards. I\'d recommend filing a complaint with the State Food Commission as well — they have more enforcement power than the DM office.', 'days_ago' => 14, 'key' => '11:0'],
            ['post_index' => 11, 'user' => 'priya.sharma', 'body' => 'Aadhaar-linked biometric authentication at PDS shops was supposed to eliminate ghost beneficiaries. Has the system been implemented in Darbhanga? If yes and the fraud still continues, it means the biometric machines are being bypassed or the shop owner has found a workaround.', 'days_ago' => 13, 'key' => '11:1'],
            ['post_index' => 11, 'user' => 'fatima.begum', 'body' => '@priya.sharma The biometric machines exist but are conveniently \"not working\" most of the time. The shop owner distributes rations based on a handwritten register instead. We\'ve raised this with the block officer but he says \"technical issues are being resolved.\" It\'s been 8 months.', 'days_ago' => 12, 'parent_key' => '11:1', 'depth' => 1, 'key' => '11:2'],

            // Post 13: Osmania Hospital (2 comments)
            ['post_index' => 13, 'user' => 'fatima.begum', 'body' => 'This mirrors what I\'ve documented in Bihar\'s government hospitals. The resident doctors are the unsung heroes of Indian healthcare — working 36-hour shifts for Rs 40,000/month while handling patient loads that would be illegal in any other country. The system runs on their exploitation.', 'days_ago' => 13, 'key' => '13:0'],
            ['post_index' => 13, 'user' => 'meera.krishnan', 'body' => 'Kerala\'s public healthcare system isn\'t perfect but it works reasonably well because of consistent investment over decades. The patient-to-doctor ratio in government hospitals here is manageable. It\'s proof that public healthcare can work in India — if governments prioritise it.', 'days_ago' => 12, 'key' => '13:1'],

            // Post 15: Mid-day meal (2 comments)
            ['post_index' => 15, 'user' => 'meera.krishnan', 'body' => 'In Kerala, the Kudumbashree SHGs that run mid-day meal kitchens are regularly trained and inspected. The key is having a strong monitoring mechanism at the school level — the School Management Committee should taste the food daily before serving it to children. This is mandated but rarely followed elsewhere.', 'days_ago' => 2, 'key' => '15:0'],
            ['post_index' => 15, 'user' => 'fatima.begum', 'body' => 'Twelve children hospitalised — and this barely made local news. If this happened in a private school, it would be national headlines for a week. The lives of government school children are simply valued less in this country. Absolutely unacceptable.', 'days_ago' => 2, 'key' => '15:1'],

            // Post 19: Onion farmers (2 comments)
            ['post_index' => 19, 'user' => 'vikram.singh', 'body' => 'The FPO (Farmer Producer Organisation) model has helped in some areas of Rajasthan. When farmers collectively negotiate, they get better prices and can even bypass APMC markets through direct procurement. But forming and sustaining an FPO requires handholding that government agencies rarely provide.', 'days_ago' => 15, 'key' => '19:0'],
            ['post_index' => 19, 'user' => 'ananya.iyer', 'body' => 'The Rs 2/kg vs Rs 40/kg gap is the clearest illustration of how broken our agricultural supply chain is. This price difference represents the inefficiency, corruption, and exploitation built into the system. Digital platforms like eNAM were supposed to fix this — has anyone in Nashik used it successfully?', 'days_ago' => 14, 'key' => '19:1'],

            // Post 24: Jaipur groundwater (3 comments, 1 nested)
            ['post_index' => 24, 'user' => 'ananya.iyer', 'body' => 'Chennai faced a Day Zero scare in 2019 and it was a wake-up call. Since then, rainwater harvesting enforcement has improved somewhat, but it\'s still not where it should be. Jaipur should study Chennai\'s experience — both the failures that led to the crisis and the reforms that followed.', 'days_ago' => 20, 'key' => '24:0'],
            ['post_index' => 24, 'user' => 'arjun.patel', 'body' => 'The traditional stepwells of Rajasthan are engineering marvels designed precisely for groundwater recharge. Restoring even 50 of Jaipur\'s historical baoris could significantly improve the water table. Israel manages water in a desert — we have traditional solutions that just need revival.', 'days_ago' => 19, 'key' => '24:1'],
            ['post_index' => 24, 'user' => 'vikram.singh', 'body' => '@arjun.patel Absolutely right. I\'ve been pushing for a \"Baori Revival Mission\" at the state level. Two stepwells in Nahargarh area were restored last year as a pilot project and are already showing improved groundwater levels in a 500m radius. We need to scale this up.', 'days_ago' => 18, 'parent_key' => '24:1', 'depth' => 1, 'key' => '24:2'],
        ];
    }

    private function generatePlaceholderImages(array $postIdsByIndex, array $postImageMap): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            return; // GD not available, skip
        }

        $colors = [
            [52, 73, 94],    // dark blue-grey
            [142, 68, 173],  // purple
            [39, 174, 96],   // green
            [41, 128, 185],  // blue
            [192, 57, 43],   // red
            [243, 156, 18],  // orange
            [22, 160, 133],  // teal
            [44, 62, 80],    // dark navy
        ];

        foreach ($postImageMap as $index => $labels) {
            if (empty($labels) || !isset($postIdsByIndex[$index])) {
                continue;
            }

            $postId = $postIdsByIndex[$index];
            $dir = storage_path("app/public/posts/{$postId}");
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            foreach ($labels as $sortOrder => $label) {
                $width = 800;
                $height = 600;
                $img = imagecreatetruecolor($width, $height);

                // Pick a color based on index
                $c = $colors[($index + $sortOrder) % count($colors)];
                $bg = imagecolorallocate($img, $c[0], $c[1], $c[2]);
                imagefill($img, 0, 0, $bg);

                // Add text
                $white = imagecolorallocate($img, 255, 255, 255);
                $grey = imagecolorallocate($img, 200, 200, 200);

                // Center the label text (using built-in font 5)
                $font = 5;
                $charWidth = imagefontwidth($font);
                $charHeight = imagefontheight($font);

                // Main label — may need wrapping
                $maxChars = intval($width / $charWidth) - 4;
                $lines = [];
                $words = explode(' ', $label);
                $currentLine = '';
                foreach ($words as $word) {
                    $testLine = $currentLine ? "{$currentLine} {$word}" : $word;
                    if (strlen($testLine) <= $maxChars) {
                        $currentLine = $testLine;
                    } else {
                        $lines[] = $currentLine;
                        $currentLine = $word;
                    }
                }
                $lines[] = $currentLine;

                $totalHeight = count($lines) * ($charHeight + 5);
                $startY = ($height - $totalHeight) / 2;

                foreach ($lines as $li => $line) {
                    $x = ($width - strlen($line) * $charWidth) / 2;
                    $y = $startY + $li * ($charHeight + 5);
                    imagestring($img, $font, (int) $x, (int) $y, $line, $white);
                }

                // Subtitle
                $sub = '[Demo placeholder image]';
                $subX = ($width - strlen($sub) * imagefontwidth(3)) / 2;
                imagestring($img, 3, (int) $subX, $height - 40, $sub, $grey);

                $filename = 'image_' . ($sortOrder + 1) . '.jpg';
                $filepath = "{$dir}/{$filename}";
                imagejpeg($img, $filepath, 85);
                imagedestroy($img);

                DB::table('post_images')->insert([
                    'post_id' => $postId,
                    'image_path' => "posts/{$postId}/{$filename}",
                    'thumbnail_path' => null,
                    'sort_order' => $sortOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
