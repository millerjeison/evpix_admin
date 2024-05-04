<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->string('description', 500);
            $table->timestamps();
        });

        Schema::create('web_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tag');
            $table->string('heading');
            $table->string('content',500)->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        DB::table('web_settings')->insert([
            [
                'name'=> 'about_us',
                'tag' => 'About Us',
                'heading' => 'Cutting-Edge Education That Empowers',
                'content' => 'Lorem ipsum dolor sit amet consectetur. Faucibus non mauris risus enim sed. Lectus fusce elit duis dignissim aliquet nisl vitae. Eget sit nisi vulputate enim sem. Facilisi tincidunt donec interdum in in eros quisque consectetur sit. Sagittis purus velit amet risus egestas sed arcu nam. Pellentesque pharetra blandit fringilla volutpat tristique sit. Sit euismod praesent volutpat eu et. Id egestas dictum cursus purus morbi semper praesent quam. Facilisis mattis amet consectetur enim aliquam. Id sed nibh ullamcorper netus ut urna eros magna. Magna orci augue volutpat turpis eget. Sed diam morbi massa at molestie. Velit tincidunt eros sit euismod. Nunc viverra porttitor mi gravida pharetra augue gravida imperdiet tempus. Fermentum faucibus scelerisque quisque lorem.',
                'image' => 'websettings/content/aboutus.png',
            ],
            [
                'name'=> 'who_we_are',
                'tag' => 'Who we are',
                'heading' => 'Empowering Minds, Shaping Futures',
                'content' => 'Lorem ipsum dolor sit amet consectetur. Nunc vel vehicula turpis ac tristique sit condimentum in. Amet ac egestas in commodo sed at. Amet dis sit porttitor sed suspendisse viverra dolor.Gravida non neque ac vitae semper nisi. Sapien quis tempor facilisis sed tincidunt sapien. Lobortis sollicitudin mi dolor aliquam ultricies.',
                'image' => 'websettings/content/whoweare.png',
            ],
            [
                'name'=> 'teacher',
                'tag' => 'Our Expert Teacher',
                'heading' => 'More Than Just Teachers, They are Mentors',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'events',
                'tag' => 'Our Events and News',
                'heading' => 'Don`t Miss the Biggest Events and News of the Year!',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'programs',
                'tag' => 'Educational Programs',
                'heading' => 'Educational Programs for every Stage',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'photos',
                'tag' => 'Our Photos',
                'heading' => 'Capturing Memories,Building Dreams',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'videos',
                'tag' => 'Our Videos',
                'heading' => 'Rewind, Replay, Rejoice! Dive into Our Video Vault',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'faqs',
                'tag' => 'FAQs',
                'heading' => 'Got Questions? We have Got Answers! Dive into Our FAQs',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'app',
                'tag' => 'Download Our School Apps!',
                'heading' => 'Empower Everyone: Teachers, Students, Parents - Get the App Now!',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'image' => null,
            ],
            [
                'name'=> 'question',
                'tag' => 'Got a Question?',
                'heading' => 'Admissions, Academics, Support:Find Your Answer Here!',
                'content' => null,
                'image' => null,
            ],
        ]);

        Schema::create('educational_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('type')->comment('1-image 2-video');
            $table->string('thumbnail')->nullable();
            $table->string('youtube_url')->nullable();
            $table->integer('session_year_id')->nullable();
            $table->timestamps();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('media_id')->nullable(true)->unsigned();
            $table->foreign('media_id')->references('id')->on('medias')->onUpdate('cascade')->onDelete('cascade');
            $table->string('file_url');
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question',1024);
            $table->string('answer',1024);
            $table->bigInteger('status');
            $table->timestamps();
        });

        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->date('date')->nullable();
            $table->string('message',1024);
            $table->timestamps();
        });

        DB::table('settings')->insert([
            [
                'type' => 'secondary_color',
                'message' => '#38A3A5',
             ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('web_settings');
        Schema::dropIfExists('events');
        Schema::dropIfExists('educational_programs');
        Schema::dropIfExists('medias');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('contact_us');
    }
};
