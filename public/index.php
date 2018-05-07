<?php

require_once '../define.php';
if (!isset($_GET['key']) || $_GET['key'] != CONSTANTS::KEY) {
        exit();
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Walpurgisnacht...</title>
	<style>
		#app {
			max-width: 1024px;
			margin: 0 auto;
		}

		.timetable {
			border: 2px solid #000000;
			margin: 0 auto;
		}
		.timetable th {
			width: 10em;
		}
		.timetable td {
			width: 10em;
			background-color: #a1cfea;
			text-align: center;
		}

		.images {
			display: flex;
		}
		img {
			display: inline-block;
			max-width: 10em;
		}
		.images span {
			display: block;
		}

		.kanzaki_ranko {
			position: fixed;
			right: 0;
			bottom: 0;

			z-index: -999;
		}
	</style>

</head>
<body>
	<div id="app">
		<img src="./kanzaki_ranko.png" alt="" class="kanzaki_ranko">
		<h1>Walpurgisnacht...</h1>

		<h3>時の理</h3>
		<input type="date" v-model="today_input" @change="change_date(date_timestamp(new Date(today_input)))">

		<div v-if="!timetable_editing">
			<table class="timetable">
				<tr>
					<th>1</th>
					<th>2</th>
					<th>3</th>
					<th>4</th>
					<th>5</th>
					<th>6</th>
					<th>7</th>
					<th>8</th>
				</tr>
				<tr>
					<td v-for="t in timetable_table()" :colspan="t.colspan">{{ t.content }}</td>
				</tr>
			</table>
			<button @click="timetable_editing = true">編集する</button>
		</div>

		<div v-else>
			<table class="timetable">
				<tr>
					<th>1</th>
					<th>2</th>
					<th>3</th>
					<th>4</th>
					<th>5</th>
					<th>6</th>
					<th>7</th>
					<th>8</th>
				</tr>
				<tr>
					<td v-for="(t, i) in timetable"><input type="text" v-model="timetable[i]" /></td>
				</tr>
			</table>
			<button @click="timetable_editing = false; set_timetable();">この曜日の時間割を更新</button>
			<button @click="timetable_editing = false; set_timetable_for_date();">この日の時間割を更新</button>
			<button @click="timetable_editing = false;">やめる</button>
		</div>


		<h3>予言の書</h3>
		<table>
			<tr v-for="event in events">
				<td>{{ event.date | date_show }}</td>
				<td>{{ event.content }}</td>
				<td @click="edit_event(event)">編集</td>
				<td @click="delete_event(event)">削除</td>
			</tr>
		</table>
		<form @submit.prevent="add_event()">
			<input type="date" v-model="new_event_date">
			<input type="text" v-model="new_event_content" placeholder="なにか">
			<button>追加</button>
		</form>


		<h3>グリモワールの破片</h3>

		<ul>
			<li v-for="memo in memos">{{ memo.content }}  <small @click="edit_memo(memo)">編集</small> <small @click="delete_memo(memo)">削除</small></li>
		</ul>

		<form @submit.prevent="add_memo()">
			<input type="text" v-model="new_memo_content" placeholder="めもめも">
			<button>追加</button>
		</form>

		<h3>言霊の記憶</h3>
		<p>
			__TIMETABLE__ が時間割に、 __1__ や __2__ が画像に置換されます
		</p>
		<table>
			<tr v-for="serif in serifs">
				<td>{{ serif.content }}</td>
				<td @click="edit_serif(serif)">編集</td>
				<td @click="delete_serif(serif)">削除</td>
			</tr>
		</table>

		<form @submit.prevent="add_serif()">
			<textarea v-model="new_serif_content" cols="30" rows="10"></textarea>
			<button>追加</button>
		</form>

    <h3>熊本の言葉</h3>
    <form @submit.prevent="speak()">
      <textarea v-model="ranko_speak" cols="30" rows="10"></textarea>
      <button>言う</button>
    </form>

		<h3>鏡像</h3>
		<div class="images">
			<div v-for="image in images">
				<img :src="image" alt="">
				<span>{{ image }}</span>
			</div>
		</div>


		<form method="post" action="api.php?query=upload_image" enctype="multipart/form-data">
			<input type="file" name="file" required />
			<button>画像をアップロード</button>
		</form>

	</div>
	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
	<script>

		let date_format = function(date, glue) {
			if (! date) {
				return "";
			}
			let y = date.getFullYear();
			let m = date.getMonth() + 1;
			let d = date.getDate();
			m = ('0'+ m).slice(-2);
			d = ('0'+ d).slice(-2);

			return "" + y + glue + m + glue + d;
		};

		let get_date = function(d) {
			let t = new Date(d);
			t.setHours(0);
			t.setMinutes(0);
			t.setSeconds(0);
			t.setMilliseconds(0);

			return t;
		};
		new Vue({
			el: '#app',
			data: {
				timetable_editing: false,

				new_event_date: null,
				new_event_content: "",

				new_memo_content: "",

				new_serif_content: "",

				events: [],
				memos: [],
				serifs: [],
				images: [],

				timetable: ['', '', '', '', '', '', '', ''],

				today: null,
				today_input: null,
        ranko_speak: "",
			},
			methods: {
				get_memos: function() {
					fetch('api.php?query=get_memos')
						.then(r => r.json())
						.then(r => {
							this.memos = r;
						});
				},
				add_memo: function() {
					let memo_content = this.new_memo_content.trim();
					if (memo_content) {
						this.new_memo_content = "";

						fetch('api.php?query=add_memo&content=' + encodeURI(memo_content))
							.then(_ => { this.get_memos(); });
					}
				},
				edit_memo: function(memo) {
					this.new_memo_content = memo.content;
					this.delete_memo(memo);
				},
				delete_memo: function(memo) {
						fetch('api.php?query=delete_memo&id=' + encodeURI(memo.id))
						.then(_ => { this.get_memos(); });
				},

				set_event: function(events) {
						this.events = [];
						for (let i = 0; i < events.length; i++) {
							events[i]['date'] = new Date(+events[i]['date'] *1000);
							this.events.push(events[i]);
						}
				},
				get_events: function(date) {
					fetch('api.php?query=get_events&date='+encodeURI(date))
						.then(r => r.json())
						.then(r => {
							this.set_event(r);
						});
				},
				add_event: function() {
					let event_date = new Date(this.new_event_date);
					let event_content = this.new_event_content.trim();

					if (event_date && event_content) {
						let t = event_date.getTime() / 1000;
						
						this.new_event_date = date_format(new Date(), "-");
						this.new_event_content = "";

						fetch('api.php?query=add_event&content=' + encodeURI(event_content) + '&date=' + encodeURI(t))
						.then(_ => { this.get_events(this.today); });
					}
				},
				edit_event: function(event) {
					this.new_event_date = date_format(event.date, "-");
					this.new_event_content = event.content;

					this.delete_event(event);
				},
				delete_event: function(event) {
					fetch('api.php?query=delete_event&id='+encodeURI(event.id))
						.then(_ => { this.get_events(this.today); });
				},
				get_serifs: function() {
					fetch('api.php?query=get_serifs')
						.then(r => r.json())
						.then(r => {
							this.serifs = r;
						});
				},
				add_serif: function() {
					let serif_content = this.new_serif_content.trim();
					if (serif_content) {
						this.new_serif_content = "";

						fetch('api.php?query=add_serif&content=' + encodeURI(serif_content))
							.then(_ => { this.get_serifs(); });
					}
				},
				edit_serif: function(serif) {
					this.new_serif_content = serif.content;
					this.delete_serif(serif);
				},
				delete_serif: function(serif) {
						fetch('api.php?query=delete_serif&id=' + encodeURI(serif.id))
						.then(_ => { this.get_serifs(); });
				},

				get_timetable: function(date) {
					fetch('api.php?query=get_timetable&date='+encodeURI(date))
						.then(r => r.json())
						.then(r => {
							if (r.hasOwnProperty("classes")) {
								this.timetable = r.classes;
							}
							else {
								this.timetable = ['', '', '', '', '', '', '', ''];
							}
						});
				},
				set_timetable: function() {
					let query = '';
					for (let i = 0; i < this.timetable.length; i++) {
						query = query + '&' + (i+1) + '=' + encodeURI(this.timetable[i]);
					}

					fetch('api.php?query=set_timetable&date='+this.today+query);
					this.get_timetable(this.today);
				},
				set_timetable_for_date: function() {
					let query = '';
					for (let i = 0; i < this.timetable.length; i++) {
						query = query + '&' + (i+1) + '=' + encodeURI(this.timetable[i]);
					}

					fetch('api.php?query=set_timetable_for_date&date='+this.today+query);
					this.get_timetable(this.today);
				},
				timetable_table: function() {
					let ret = [];

					let pre = null;
					for (let i = 0; i < this.timetable.length; i++) {
						if (! pre) {
							pre = {colspan: 1, content: this.timetable[i]};
						}
						else if (pre.content == this.timetable[i]) {
							pre.colspan++;
						}
						else {
							ret.push(pre);
							pre = {colspan: 1, content: this.timetable[i]};
						}
					}

					if (pre) {
						ret.push(pre);
					}
					return ret;
				},
				date_timestamp: function(d) {
					return get_date(d).getTime() / 1000;
				},
				today_timestamp: function() {
					return this.date_timestamp(new Date());
				},

				change_date: function(today) {
					this.today = today;
					this.today_input = date_format(new Date(today * 1000), "-");
					this.new_event_date = date_format(new Date(today * 1000), "-");

					this.get_events(this.today);
					this.get_timetable(this.today);
				},

				get_images: function() {
					fetch('api.php?query=get_images')
						.then(r => r.json())
						.then(r => {
							this.images = r;
						});
				},
				speak: function() {
					fetch('api.php?query=ranko_speak&serif='+encodeURI(this.ranko_speak));
          this.ranko_speak = "";
				}
			},
			filters: {
				date_show: function(date) {
					return date_format(date, "/");
				},
			},
			mounted: function() {
				this.change_date(this.today_timestamp());
				this.get_memos();
				this.get_serifs();
				this.get_images();
			},
		});
	</script>

</body>
</html>
