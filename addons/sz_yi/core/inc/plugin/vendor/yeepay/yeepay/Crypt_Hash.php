<?php
// Ä£¿éLTDÌá¹©
class Crypt_Hash
{
	public $b;
	public $l = false;
	public $hash;
	public $key = false;
	public $opad;
	public $ipad;

	public function Crypt_Hash($hash = 'sha1')
	{
		if (!defined('CRYPT_HASH_MODE')) {
			switch (true) {
			case extension_loaded('hash'):
				define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_HASH);
				break;

			case extension_loaded('mhash'):
				define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_MHASH);
				break;

			default:
				define('CRYPT_HASH_MODE', CRYPT_HASH_MODE_INTERNAL);
			}
		}

		$this->setHash($hash);
	}

	public function setKey($key = false)
	{
		$this->key = $key;
	}

	public function setHash($hash)
	{
		$hash = strtolower($hash);
		5	CASE	48	OP1:$hash	OP2:'md5-96'	RES:TMP:2	;0	>>7	
		6	JMPZ	43	OP1:TMP:2:'sha1-96'	OP2:UNUSED:8	RES:UNUSED:0	;0	>>8	
		7	JMP	42	OP1:UNUSED:10	OP2:UNUSED:0	RES:UNUSED:0	;0	>>10	<<5	
		8	CASE	48	OP1:$hash	OP2:'sha1-96'	RES:TMP:2	;0	>>10	<<6	
		9	JMPZ	43	OP1:TMP:2:'sha1-96'	OP2:UNUSED:13	RES:UNUSED:0	;0	>>13	
		10	ASSIGN_OBJ	136	OP1:UNUSED:1498856	OP2:'l'	RES:	;268435456	<<7,8	
		11	OP_DATA	137	OP1:12	OP2:UNUSED:0	RES:UNUSED:0	;0	
		12	BRK	50	OP1:UNUSED:0	OP2:1	RES:UNUSED:0	;0	
		==

		$this->l = 12;
		break;
		13	CASE	48	OP1:$hash	OP2:'md2'	RES:TMP:2	;0	>>15	<<9	
		14	JMPZ	43	OP1:TMP:2:'md5'	OP2:UNUSED:16	RES:UNUSED:0	;0	>>16	
		15	JMP	42	OP1:UNUSED:18	OP2:UNUSED:0	RES:UNUSED:0	;0	>>18	<<13	
		16	CASE	48	OP1:$hash	OP2:'md5'	RES:TMP:2	;0	>>18	<<14	
		17	JMPZ	43	OP1:TMP:2:'md5'	OP2:UNUSED:21	RES:UNUSED:0	;0	>>21	
		18	ASSIGN_OBJ	136	OP1:UNUSED:42364368	OP2:'l'	RES:	;268435456	<<15,16	
		19	OP_DATA	137	OP1:16	OP2:UNUSED:0	RES:UNUSED:0	;0	
		20	BRK	50	OP1:UNUSED:0	OP2:1	RES:UNUSED:0	;0	
		==

		$this->l = 16;
		break;
		21	CASE	48	OP1:$hash	OP2:'sha1'	RES:TMP:2	;0	>>23	<<17	
		22	JMPZ	43	OP1:TMP:2:'sha1'	OP2:UNUSED:26	RES:UNUSED:0	;0	>>26	
		23	ASSIGN_OBJ	136	OP1:UNUSED:1427016	OP2:'l'	RES:	;268435456	<<21	
		24	OP_DATA	137	OP1:20	OP2:UNUSED:0	RES:UNUSED:0	;0	
		25	BRK	50	OP1:UNUSED:0	OP2:1	RES:UNUSED:0	;0	
		==

		$this->l = 20;
		break;
		26	CASE	48	OP1:$hash	OP2:'sha256'	RES:TMP:2	;0	>>28	<<22	
		27	JMPZ	43	OP1:TMP:2:'sha256'	OP2:UNUSED:31	RES:UNUSED:0	;0	>>31	
		28	ASSIGN_OBJ	136	OP1:UNUSED:42352096	OP2:'l'	RES:	;268435456	<<26	
		29	OP_DATA	137	OP1:32	OP2:UNUSED:0	RES:UNUSED:0	;0	
		30	BRK	50	OP1:UNUSED:0	OP2:1	RES:UNUSED:0	;0	
		==

		$this->l = 32;
		break;
		31	CASE	48	OP1:$hash	OP2:'sha384'	RES:TMP:2	;0	>>33	<<27	
		32	JMPZ	43	OP1:TMP:2:'sha384'	OP2:UNUSED:36	RES:UNUSED:0	;0	>>36	
		33	ASSIGN_OBJ	136	OP1:UNUSED:42496072	OP2:'l'	RES:	;268435456	<<31	
		34	OP_DATA	137	OP1:48	OP2:UNUSED:0	RES:UNUSED:0	;0	
		35	BRK	50	OP1:UNUSED:0	OP2:1	RES:UNUSED:0	;0	
		==

		$this->l = 48;
		break;
		36	CASE	48	OP1:$hash	OP2:'sha512'	RES:TMP:2	;0	>>38	<<32	
		37	JMPZ	43	OP1:TMP:2:'sha512'	OP2:UNUSED:40	RES:UNUSED:0	;0	>>40	
		38	ASSIGN_OBJ	136	OP1:UNUSED:43748480	OP2:'l'	RES:	;268435456	<<36	
		39	OP_DATA	137	OP1:64	OP2:UNUSED:0	RES:UNUSED:0	;0	
		==

		$this->l = 64;
		40	CASE	48	OP1:$hash	OP2:'md2'	RES:TMP:9	;0	>>42	<<37	
		41	JMPZ	43	OP1:TMP:9:'md2'	OP2:UNUSED:61	RES:UNUSED:0	;0	>>61	
		42	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE'	RES:TMP:10	;16	<<40	
		43	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE_HASH'	RES:TMP:11	;16	
		44	IS_EQUAL	17	OP1:TMP:10:CRYPT_HASH_MODE	OP2:TMP:11:CRYPT_HASH_MODE_HASH	RES:TMP:12	;0	
		45	JMPZ_EX	46	OP1:TMP:12:in_array('md2', hash_algos())	OP2:UNUSED:53	RES:TMP:12	;0	>>53	
		46	INIT_FCALL_BY_NAME	59	OP1:'in_array'	OP2:'in_array'	RES:UNUSED:0	;-1193253318	
		47	SEND_VAL	65	OP1:'md2'	OP2:UNUSED:1	RES:UNUSED:0	;61	
		48	INIT_FCALL_BY_NAME	59	OP1:'hash_algos'	OP2:'hash_algos'	RES:UNUSED:1	;1790398398	
		49	DO_FCALL_BY_NAME	61	OP1:UNUSED:0	OP2:UNUSED:1	RES:VAR:13	;0	
		50	SEND_VAR_NO_REF	106	OP1:VAR:13:hash_algos()	OP2:UNUSED:2	RES:UNUSED:0	;4	
		51	DO_FCALL_BY_NAME	61	OP1:UNUSED:0	OP2:UNUSED:0	RES:VAR:14	;2	
		52	BOOL	52	OP1:VAR:14:in_array('md2', hash_algos())	OP2:UNUSED:0	RES:TMP:12	;0	
		53	JMPZ	43	OP1:TMP:12:in_array('md2', hash_algos())	OP2:UNUSED:57	RES:UNUSED:0	;0	>>57	<<45	
		54	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE_HASH'	RES:TMP:15	;16	
		55	QM_ASSIGN	22	OP1:TMP:15:CRYPT_HASH_MODE_HASH	OP2:UNUSED:0	RES:TMP:16	;0	
		56	JMP	42	OP1:UNUSED:59	OP2:UNUSED:0	RES:UNUSED:0	;0	>>59	
		57	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE_INTERNAL'	RES:TMP:17	;16	<<53	
		58	QM_ASSIGN	22	OP1:TMP:17:CRYPT_HASH_MODE_INTERNAL	OP2:UNUSED:0	RES:TMP:16	;0	
		59	ASSIGN	38	OP1:$mode	OP2:TMP:16:CRYPT_HASH_MODE_INTERNAL	RES:	;0	<<56	
		60	BRK	50	OP1:UNUSED:1	OP2:1	RES:UNUSED:0	;0	
		==

		$mode = CRYPT_HASH_MODE_INTERNAL;
		break;
		61	CASE	48	OP1:$hash	OP2:'sha384'	RES:TMP:9	;0	>>63	<<41	
		62	JMPZ	43	OP1:TMP:9:'sha512'	OP2:UNUSED:64	RES:UNUSED:0	;0	>>64	
		63	JMP	42	OP1:UNUSED:66	OP2:UNUSED:0	RES:UNUSED:0	;0	>>66	<<61	
		64	CASE	48	OP1:$hash	OP2:'sha512'	RES:TMP:9	;0	>>66	<<62	
		65	JMPZ	43	OP1:TMP:9:'sha512'	OP2:UNUSED:77	RES:UNUSED:0	;0	>>77	
		66	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE'	RES:TMP:19	;16	<<63,64	
		67	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE_MHASH'	RES:TMP:20	;16	
		68	IS_EQUAL	17	OP1:TMP:19:CRYPT_HASH_MODE	OP2:TMP:20:CRYPT_HASH_MODE_MHASH	RES:TMP:21	;0	
		69	JMPZ	43	OP1:TMP:21:CRYPT_HASH_MODE == CRYPT_HASH_MODE_MHASH	OP2:UNUSED:73	RES:UNUSED:0	;0	>>73	
		70	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE_INTERNAL'	RES:TMP:22	;16	
		71	QM_ASSIGN	22	OP1:TMP:22:CRYPT_HASH_MODE_INTERNAL	OP2:UNUSED:0	RES:TMP:23	;0	
		72	JMP	42	OP1:UNUSED:75	OP2:UNUSED:0	RES:UNUSED:0	;0	>>75	
		73	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'CRYPT_HASH_MODE'	RES:TMP:24	;16	<<69	
		74	QM_ASSIGN	22	OP1:TMP:24:CRYPT_HASH_MODE	OP2:UNUSED:0	RES:TMP:23	;0	
		75	ASSIGN	38	OP1:$mode	OP2:TMP:23:CRYPT_HASH_MODE	RES:	;0	<<72	
		76	BRK	50	OP1:UNUSED:1	OP2:1	RES:UNUSED:0	;0	
		==

		$mode = CRYPT_HASH_MODE;
		break;

		$mode = CRYPT_HASH_MODE;

		goto label80;
label80:
		81	CASE	48	OP1:$mode	OP2:TMP:28:CRYPT_HASH_MODE_MHASH	RES:TMP:29	;0	>>83	
		82	JMPZ	43	OP1:TMP:29:CRYPT_HASH_MODE_MHASH	OP2:UNUSED:109	RES:UNUSED:0	;0	>>109	
		83	CASE	48	OP1:$hash	OP2:'md5'	RES:TMP:30	;0	>>85	<<81	
		84	JMPZ	43	OP1:TMP:30:'sha1-96'	OP2:UNUSED:86	RES:UNUSED:0	;0	>>86	
		85	JMP	42	OP1:UNUSED:88	OP2:UNUSED:0	RES:UNUSED:0	;0	>>88	<<83	
		86	CASE	48	OP1:$hash	OP2:'md5-96'	RES:TMP:30	;0	>>88	<<84	
		87	JMPZ	43	OP1:TMP:30:'sha1-96'	OP2:UNUSED:92	RES:UNUSED:0	;0	>>92	
		88	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'MHASH_MD5'	RES:TMP:32	;16	<<85,86	
		89	ASSIGN_OBJ	136	OP1:UNUSED:42400472	OP2:'hash'	RES:	;1073741824	
		90	OP_DATA	137	OP1:TMP:32:MHASH_MD5	OP2:UNUSED:0	RES:UNUSED:0	;0	
		91	BRK	50	OP1:UNUSED:3	OP2:1	RES:UNUSED:0	;0	
		92	CASE	48	OP1:$hash	OP2:'sha256'	RES:TMP:30	;0	>>94	<<87	
		93	JMPZ	43	OP1:TMP:30:'sha1-96'	OP2:UNUSED:98	RES:UNUSED:0	;0	>>98	
		94	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'MHASH_SHA256'	RES:TMP:34	;16	<<92	
		95	ASSIGN_OBJ	136	OP1:UNUSED:42400536	OP2:'hash'	RES:	;1073741824	
		96	OP_DATA	137	OP1:TMP:34:MHASH_SHA256	OP2:UNUSED:0	RES:UNUSED:0	;0	
		97	BRK	50	OP1:UNUSED:3	OP2:1	RES:UNUSED:0	;0	
		98	CASE	48	OP1:$hash	OP2:'sha1'	RES:TMP:30	;0	>>100	<<93	
		99	JMPZ	43	OP1:TMP:30:'sha1-96'	OP2:UNUSED:101	RES:UNUSED:0	;0	>>101	
		100	JMP	42	OP1:UNUSED:104	OP2:UNUSED:0	RES:UNUSED:0	;0	>>104	<<98	
		101	CASE	48	OP1:$hash	OP2:'sha1-96'	RES:TMP:30	;0	>>103	<<99	
		102	JMPZ	43	OP1:TMP:30:'sha1-96'	OP2:UNUSED:104	RES:UNUSED:0	;0	>>104	
		103	JMP	42	OP1:UNUSED:104	OP2:UNUSED:0	RES:UNUSED:0	;0	>>104	<<101	
		104	FETCH_CONSTANT	99	OP1:UNUSED:0	OP2:'MHASH_SHA1'	RES:TMP:36	;16	<<100,102,103	
		105	ASSIGN_OBJ	136	OP1:UNUSED:45444732	OP2:'hash'	RES:	;1073741824	
		106	OP_DATA	137	OP1:TMP:36:MHASH_SHA1	OP2:UNUSED:0	RES:UNUSED:0	;0	
		107	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		108	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		==
		$this->hash = MHASH_MD5;
		break;
		$this->hash = MHASH_SHA256;
		break;
		$this->hash = MHASH_SHA1;
		return NULL;
		return NULL;
		110	CASE	48	OP1:$mode	OP2:TMP:37:CRYPT_HASH_MODE_HASH	RES:TMP:29	;0	>>112	
		111	JMPZ	43	OP1:TMP:29:CRYPT_HASH_MODE_HASH	OP2:UNUSED:144	RES:UNUSED:0	;0	>>144	
		112	CASE	48	OP1:$hash	OP2:'md5'	RES:TMP:38	;0	>>114	<<110	
		113	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:115	RES:UNUSED:0	;0	>>115	
		114	JMP	42	OP1:UNUSED:117	OP2:UNUSED:0	RES:UNUSED:0	;0	>>117	<<112	
		115	CASE	48	OP1:$hash	OP2:'md5-96'	RES:TMP:38	;0	>>117	<<113	
		116	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:120	RES:UNUSED:0	;0	>>120	
		117	ASSIGN_OBJ	136	OP1:UNUSED:1548180	OP2:'hash'	RES:	;1073741824	<<114,115	
		118	OP_DATA	137	OP1:'md5'	OP2:UNUSED:0	RES:UNUSED:0	;0	
		119	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		120	CASE	48	OP1:$hash	OP2:'md2'	RES:TMP:38	;0	>>122	<<116	
		121	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:123	RES:UNUSED:0	;0	>>123	
		122	JMP	42	OP1:UNUSED:131	OP2:UNUSED:0	RES:UNUSED:0	;0	>>131	<<120	
		123	CASE	48	OP1:$hash	OP2:'sha256'	RES:TMP:38	;0	>>125	<<121	
		124	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:126	RES:UNUSED:0	;0	>>126	
		125	JMP	42	OP1:UNUSED:131	OP2:UNUSED:0	RES:UNUSED:0	;0	>>131	<<123	
		126	CASE	48	OP1:$hash	OP2:'sha384'	RES:TMP:38	;0	>>128	<<124	
		127	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:129	RES:UNUSED:0	;0	>>129	
		128	JMP	42	OP1:UNUSED:131	OP2:UNUSED:0	RES:UNUSED:0	;0	>>131	<<126	
		129	CASE	48	OP1:$hash	OP2:'sha512'	RES:TMP:38	;0	>>131	<<127	
		130	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:134	RES:UNUSED:0	;0	>>134	
		131	ASSIGN_OBJ	136	OP1:UNUSED:42073328	OP2:'hash'	RES:	;1073741824	<<122,125,128,129	
		132	OP_DATA	137	OP1:$hash	OP2:UNUSED:0	RES:UNUSED:0	;0	
		133	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		134	CASE	48	OP1:$hash	OP2:'sha1'	RES:TMP:38	;0	>>136	<<130	
		135	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:137	RES:UNUSED:0	;0	>>137	
		136	JMP	42	OP1:UNUSED:140	OP2:UNUSED:0	RES:UNUSED:0	;0	>>140	<<134	
		137	CASE	48	OP1:$hash	OP2:'sha1-96'	RES:TMP:38	;0	>>139	<<135	
		138	JMPZ	43	OP1:TMP:38:'sha1-96'	OP2:UNUSED:140	RES:UNUSED:0	;0	>>140	
		139	JMP	42	OP1:UNUSED:140	OP2:UNUSED:0	RES:UNUSED:0	;0	>>140	<<137	
		140	ASSIGN_OBJ	136	OP1:UNUSED:45930752	OP2:'hash'	RES:	;1073741824	<<136,138,139	
		141	OP_DATA	137	OP1:'sha1'	OP2:UNUSED:0	RES:UNUSED:0	;0	
		142	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		143	RETURN	62	OP1:NULL	OP2:UNUSED:0	RES:UNUSED:0	;0	
		==

		$this->hash = 'md5';
		return NULL;
		$this->hash = $hash;
		return NULL;
		$this->hash = 'sha1';
		return NULL;
		return NULL;
		144	CASE	48	OP1:$hash	OP2:'md2'	RES:TMP:42	;0	>>146	<<111	
		145	JMPZ	43	OP1:TMP:42:'md2'	OP2:UNUSED:153	RES:UNUSED:0	;0	>>153	
		146	ASSIGN_OBJ	136	OP1:UNUSED:45492804	OP2:'b'	RES:	;268435456	<<144	
		147	OP_DATA	137	OP1:16	OP2:UNUSED:0	RES:UNUSED:0	;0	
		148	INIT_ARRAY	71	OP1:$this	OP2:UNUSED:0	RES:TMP:46	;0	
		149	ADD_ARRAY_ELEMENT	72	OP1:'_md2'	OP2:UNUSED:0	RES:TMP:46	;0	
		150	ASSIGN_OBJ	136	OP1:UNUSED:1447312	OP2:'hash'	RES:	;1073741824	
		151	OP_DATA	137	OP1:TMP:46:array($this, '_md2')	OP2:UNUSED:0	RES:UNUSED:0	;0	
		152	BRK	50	OP1:UNUSED:5	OP2:1	RES:UNUSED:0	;0	
		==

		$this->b = 16;
		$this->hash = array($this, '_md2');
		break;
		153	CASE	48	OP1:$hash	OP2:'md5'	RES:TMP:42	;0	>>155	<<145	
		154	JMPZ	43	OP1:TMP:42:'md5-96'	OP2:UNUSED:156	RES:UNUSED:0	;0	>>156	
		155	JMP	42	OP1:UNUSED:158	OP2:UNUSED:0	RES:UNUSED:0	;0	>>158	<<153	
		156	CASE	48	OP1:$hash	OP2:'md5-96'	RES:TMP:42	;0	>>158	<<154	
		157	JMPZ	43	OP1:TMP:42:'md5-96'	OP2:UNUSED:165	RES:UNUSED:0	;0	>>165	
		158	ASSIGN_OBJ	136	OP1:UNUSED:1425072	OP2:'b'	RES:	;268435456	<<155,156	
		159	OP_DATA	137	OP1:64	OP2:UNUSED:0	RES:UNUSED:0	;0	
		160	INIT_ARRAY	71	OP1:$this	OP2:UNUSED:0	RES:TMP:50	;0	
		161	ADD_ARRAY_ELEMENT	72	OP1:'_md5'	OP2:UNUSED:0	RES:TMP:50	;0	
		162	ASSIGN_OBJ	136	OP1:UNUSED:42747944	OP2:'hash'	RES:	;1073741824	
		163	OP_DATA	137	OP1:TMP:50:array($this, '_md5')	OP2:UNUSED:0	RES:UNUSED:0	;0	
		164	BRK	50	OP1:UNUSED:5	OP2:1	RES:UNUSED:0	;0	
		==

		$this->b = 64;
		$this->hash = array($this, '_md5');
		break;
		165	CASE	48	OP1:$hash	OP2:'sha256'	RES:TMP:42	;0	>>167	<<157	
		166	JMPZ	43	OP1:TMP:42:'sha256'	OP2:UNUSED:174	RES:UNUSED:0	;0	>>174	
		167	ASSIGN_OBJ	136	OP1:UNUSED:1493952	OP2:'b'	RES:	;268435456	<<165	
		168	OP_DATA	137	OP1:64	OP2:UNUSED:0	RES:UNUSED:0	;0	
		169	INIT_ARRAY	71	OP1:$this	OP2:UNUSED:0	RES:TMP:54	;0	
		170	ADD_ARRAY_ELEMENT	72	OP1:'_sha256'	OP2:UNUSED:0	RES:TMP:54	;0	
		171	ASSIGN_OBJ	136	OP1:UNUSED:43764960	OP2:'hash'	RES:	;1073741824	
		172	OP_DATA	137	OP1:TMP:54:array($this, '_sha256')	OP2:UNUSED:0	RES:UNUSED:0	;0	
		173	BRK	50	OP1:UNUSED:5	OP2:1	RES:UNUSED:0	;0	
		==

		$this->b = 64;
		$this->hash = array($this, '_sha256');
		break;
		174	CASE	48	OP1:$hash	OP2:'sha384'	RES:TMP:42	;0	>>176	<<166	
		175	JMPZ	43	OP1:TMP:42:'sha512'	OP2:UNUSED:177	RES:UNUSED:0	;0	>>177	
		176	JMP	42	OP1:UNUSED:179	OP2:UNUSED:0	RES:UNUSED:0	;0	>>179	<<174	
		177	CASE	48	OP1:$hash	OP2:'sha512'	RES:TMP:42	;0	>>179	<<175	
		178	JMPZ	43	OP1:TMP:42:'sha512'	OP2:UNUSED:186	RES:UNUSED:0	;0	>>186	
		179	ASSIGN_OBJ	136	OP1:UNUSED:42598956	OP2:'b'	RES:	;268435456	<<176,177	
		180	OP_DATA	137	OP1:128	OP2:UNUSED:0	RES:UNUSED:0	;0	
		181	INIT_ARRAY	71	OP1:$this	OP2:UNUSED:0	RES:TMP:58	;0	
		182	ADD_ARRAY_ELEMENT	72	OP1:'_sha512'	OP2:UNUSED:0	RES:TMP:58	;0	
		183	ASSIGN_OBJ	136	OP1:UNUSED:45917696	OP2:'hash'	RES:	;1073741824	
		184	OP_DATA	137	OP1:TMP:58:array($this, '_sha512')	OP2:UNUSED:0	RES:UNUSED:0	;0	
		185	BRK	50	OP1:UNUSED:5	OP2:1	RES:UNUSED:0	;0	
		==

		$this->b = 128;
		$this->hash = array($this, '_sha512');
		break;

		switch ($hash) {
		case 'sha1':
		case 'sha1-96':
		}

		$this->b = 64;
		$this->hash = array($this, '_sha1');

		goto label199;
label199:
		$this->ipad = str_repeat(chr(54), $this->b);
		$this->opad = str_repeat(chr(92), $this->b);
	}

	public function hash($text)
	{
		$mode = (is_array($this->hash) ? CRYPT_HASH_MODE_INTERNAL : CRYPT_HASH_MODE);
		if (!empty($this->key) || is_string($this->key)) {
			switch ($mode) {
			case CRYPT_HASH_MODE_MHASH:
				$output = mhash($this->hash, $text, $this->key);
				break;

			case CRYPT_HASH_MODE_HASH:
				$output = hash_hmac($this->hash, $text, $this->key, true);
				break;

			case CRYPT_HASH_MODE_INTERNAL:
				$key = ($this->b < strlen($this->key) ? call_user_func($this->hash, $this->key) : $this->key);
				$key = str_pad($key, $this->b, chr(0));
				$temp = $this->ipad ^ $key;
				$temp .= $text;
				$temp = call_user_func($this->hash, $temp);
				$output = $this->opad ^ $key;
				$output .= $temp;
				$output = call_user_func($this->hash, $output);
			}
		}
		else {
			switch ($mode) {
			case CRYPT_HASH_MODE_MHASH:
				$output = mhash($this->hash, $text);
				break;

			case CRYPT_HASH_MODE_HASH:
				$output = hash($this->hash, $text, true);
				break;

			case CRYPT_HASH_MODE_INTERNAL:
				$output = call_user_func($this->hash, $text);
			}
		}

		return substr($output, 0, $this->l);
	}

	public function getLength()
	{
		return $this->l;
	}

	public function _md5($m)
	{
		return pack('H*', md5($m));
	}

	public function _sha1($m)
	{
		return pack('H*', sha1($m));
	}

	public function _md2($m)
	{
		static $s = array(41, 46, 67, 201, 162, 216, 124, 1, 61, 54, 84, 161, 236, 240, 6, 19, 98, 167, 5, 243, 192, 199, 115, 140, 152, 147, 43, 217, 188, 76, 130, 202, 30, 155, 87, 60, 253, 212, 224, 22, 103, 66, 111, 24, 138, 23, 229, 18, 190, 78, 196, 214, 218, 158, 222, 73, 160, 251, 245, 142, 187, 47, 238, 122, 169, 104, 121, 145, 21, 178, 7, 63, 148, 194, 16, 137, 11, 34, 95, 33, 128, 127, 93, 154, 90, 144, 50, 39, 53, 62, 204, 231, 191, 247, 151, 3, 255, 25, 48, 179, 72, 165, 181, 209, 215, 94, 146, 42, 172, 86, 170, 198, 79, 184, 56, 210, 150, 164, 125, 182, 118, 252, 107, 226, 156, 116, 4, 241, 69, 157, 112, 89, 100, 113, 135, 32, 134, 91, 207, 101, 230, 45, 168, 2, 27, 96, 37, 173, 174, 176, 185, 246, 28, 70, 97, 105, 52, 64, 126, 15, 85, 71, 163, 35, 221, 81, 175, 58, 195, 92, 249, 206, 186, 197, 234, 38, 44, 83, 13, 110, 133, 40, 132, 9, 211, 223, 205, 244, 65, 129, 77, 82, 106, 220, 55, 200, 108, 193, 171, 250, 36, 225, 123, 8, 12, 189, 177, 74, 120, 136, 149, 139, 227, 99, 232, 109, 233, 203, 213, 254, 59, 0, 29, 57, 242, 239, 183, 14, 102, 88, 208, 228, 166, 119, 114, 248, 235, 117, 75, 10, 49, 68, 80, 180, 143, 237, 31, 26, 219, 153, 141, 51, 159, 17, 131, 20);
		$pad = 16 - (strlen($m) & 15);
		$m .= str_repeat(chr($pad), $pad);
		$length = strlen($m);
		$c = str_repeat(chr(0), 16);
		$l = chr(0);
		$i = 0;

		while ($i < $length) {
			$j = 0;

			while ($j < 16) {
				$c[$j] = chr($s[ord($m[$i + $j] ^ $l)] ^ ord($c[$j]));
				$l = $c[$j];
				++$j;
			}

			$i += 16;
		}

		$m .= $c;
		$length += 16;
		$x = str_repeat(chr(0), 48);
		$i = 0;

		while ($i < $length) {
			$j = 0;

			while ($j < 16) {
				$x[$j + 16] = $m[$i + $j];
				$x[$j + 32] = $x[$j + 16] ^ $x[$j];
				++$j;
			}

			$t = chr(0);
			$j = 0;

			while ($j < 18) {
				$k = 0;

				while ($k < 48) {
					$x[$k] = $t = $x[$k] ^ chr($s[ord($t)]);
					++$k;
				}

				$t = chr(ord($t) + $j);
				++$j;
			}

			$i += 16;
		}

		return substr($x, 0, 16);
	}

	public function _sha256($m)
	{
		$hash = array(1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225);
		static $k = array(1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221, 3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580, 3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986, 2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895, 666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037, 2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344, 430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779, 1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298);
		$length = strlen($m);
		$m .= str_repeat(chr(0), 64 - (($length + 8) & 63));
		$m[$length] = chr(128);
		$m .= pack('N2', 0, $length << 3);
		$chunks = str_split($m, 64);

		foreach ($chunks as $chunk) {
			$w = array();
			$i = 0;

			while ($i < 16) {
				$_unpack = unpack('Ntemp', $this->_string_shift($chunk, 4));
				$w[] = $_unpack['temp'];
				++$i;
			}

			$i = 16;

			while ($i < 64) {
				$s0 = $this->_rightRotate($w[$i - 15], 7) ^ $this->_rightRotate($w[$i - 15], 18) ^ $this->_rightShift($w[$i - 15], 3);
				$s1 = $this->_rightRotate($w[$i - 2], 17) ^ $this->_rightRotate($w[$i - 2], 19) ^ $this->_rightShift($w[$i - 2], 10);
				$w[$i] = $this->_add($w[$i - 16], $s0, $w[$i - 7], $s1);
				++$i;
			}

			list($a, $b, $c, $d, $e, $f, $g, $h) = $hash;
			$i = 0;

			while ($i < 64) {
				$s0 = $this->_rightRotate($a, 2) ^ $this->_rightRotate($a, 13) ^ $this->_rightRotate($a, 22);
				$maj = ($a & $b) ^ ($a & $c) ^ ($b & $c);
				$t2 = $this->_add($s0, $maj);
				$s1 = $this->_rightRotate($e, 6) ^ $this->_rightRotate($e, 11) ^ $this->_rightRotate($e, 25);
				$ch = ($e & $f) ^ ($this->_not($e) & $g);
				$t1 = $this->_add($h, $s1, $ch, $k[$i], $w[$i]);
				$h = $g;
				$g = $f;
				$f = $e;
				$e = $this->_add($d, $t1);
				$d = $c;
				$c = $b;
				$b = $a;
				$a = $this->_add($t1, $t2);
				++$i;
			}

			$hash = array($this->_add($hash[0], $a), $this->_add($hash[1], $b), $this->_add($hash[2], $c), $this->_add($hash[3], $d), $this->_add($hash[4], $e), $this->_add($hash[5], $f), $this->_add($hash[6], $g), $this->_add($hash[7], $h));
		}

		return pack('N8', $hash[0], $hash[1], $hash[2], $hash[3], $hash[4], $hash[5], $hash[6], $hash[7]);
	}

	public function _sha512($m)
	{
		if (!class_exists('Math_BigInteger')) {
			require_once 'Math/BigInteger.php';
		}

		static $init384;
		static $init512;
		static $k;

		if (!isset($k)) {
			$init384 = array('cbbb9d5dc1059ed8', '629a292a367cd507', '9159015a3070dd17', '152fecd8f70e5939', '67332667ffc00b31', '8eb44a8768581511', 'db0c2e0d64f98fa7', '47b5481dbefa4fa4');
			$init512 = array('6a09e667f3bcc908', 'bb67ae8584caa73b', '3c6ef372fe94f82b', 'a54ff53a5f1d36f1', '510e527fade682d1', '9b05688c2b3e6c1f', '1f83d9abfb41bd6b', '5be0cd19137e2179');
			$i = 0;

			while ($i < 8) {
				$init384[$i] = new Math_BigInteger($init384[$i], 16);
				$init384[$i]->setPrecision(64);
				$init512[$i] = new Math_BigInteger($init512[$i], 16);
				$init512[$i]->setPrecision(64);
				++$i;
			}

			$k = array('428a2f98d728ae22', '7137449123ef65cd', 'b5c0fbcfec4d3b2f', 'e9b5dba58189dbbc', '3956c25bf348b538', '59f111f1b605d019', '923f82a4af194f9b', 'ab1c5ed5da6d8118', 'd807aa98a3030242', '12835b0145706fbe', '243185be4ee4b28c', '550c7dc3d5ffb4e2', '72be5d74f27b896f', '80deb1fe3b1696b1', '9bdc06a725c71235', 'c19bf174cf692694', 'e49b69c19ef14ad2', 'efbe4786384f25e3', '0fc19dc68b8cd5b5', '240ca1cc77ac9c65', '2de92c6f592b0275', '4a7484aa6ea6e483', '5cb0a9dcbd41fbd4', '76f988da831153b5', '983e5152ee66dfab', 'a831c66d2db43210', 'b00327c898fb213f', 'bf597fc7beef0ee4', 'c6e00bf33da88fc2', 'd5a79147930aa725', '06ca6351e003826f', '142929670a0e6e70', '27b70a8546d22ffc', '2e1b21385c26c926', '4d2c6dfc5ac42aed', '53380d139d95b3df', '650a73548baf63de', '766a0abb3c77b2a8', '81c2c92e47edaee6', '92722c851482353b', 'a2bfe8a14cf10364', 'a81a664bbc423001', 'c24b8b70d0f89791', 'c76c51a30654be30', 'd192e819d6ef5218', 'd69906245565a910', 'f40e35855771202a', '106aa07032bbd1b8', '19a4c116b8d2d0c8', '1e376c085141ab53', '2748774cdf8eeb99', '34b0bcb5e19b48a8', '391c0cb3c5c95a63', '4ed8aa4ae3418acb', '5b9cca4f7763e373', '682e6ff3d6b2b8a3', '748f82ee5defb2fc', '78a5636f43172f60', '84c87814a1f0ab72', '8cc702081a6439ec', '90befffa23631e28', 'a4506cebde82bde9', 'bef9a3f7b2c67915', 'c67178f2e372532b', 'ca273eceea26619c', 'd186b8c721c0c207', 'eada7dd6cde0eb1e', 'f57d4f7fee6ed178', '06f067aa72176fba', '0a637dc5a2c898a6', '113f9804bef90dae', '1b710b35131c471b', '28db77f523047d84', '32caab7b40c72493', '3c9ebe0a15c9bebc', '431d67c49c100d4c', '4cc5d4becb3e42b6', '597f299cfc657e2a', '5fcb6fab3ad6faec', '6c44198c4a475817');
			$i = 0;

			while ($i < 80) {
				$k[$i] = new Math_BigInteger($k[$i], 16);
				++$i;
			}
		}

		$hash = ($this->l == 48 ? $init384 : $init512);
		$length = strlen($m);
		$m .= str_repeat(chr(0), 128 - (($length + 16) & 127));
		$m[$length] = chr(128);
		$m .= pack('N4', 0, 0, 0, $length << 3);
		$chunks = str_split($m, 128);

		foreach ($chunks as $chunk) {
			$w = array();
			$i = 0;

			while ($i < 16) {
				$temp = new Math_BigInteger($this->_string_shift($chunk, 8), 256);
				$temp->setPrecision(64);
				$w[] = $temp;
				++$i;
			}

			$i = 16;

			while ($i < 80) {
				$temp = array($w[$i - 15]->bitwise_rightRotate(1), $w[$i - 15]->bitwise_rightRotate(8), $w[$i - 15]->bitwise_rightShift(7));
				$s0 = $temp[0]->bitwise_xor($temp[1]);
				$s0 = $s0->bitwise_xor($temp[2]);
				$temp = array($w[$i - 2]->bitwise_rightRotate(19), $w[$i - 2]->bitwise_rightRotate(61), $w[$i - 2]->bitwise_rightShift(6));
				$s1 = $temp[0]->bitwise_xor($temp[1]);
				$s1 = $s1->bitwise_xor($temp[2]);
				$w[$i] = $w[$i - 16]->copy();
				$w[$i] = $w[$i]->add($s0);
				$w[$i] = $w[$i]->add($w[$i - 7]);
				$w[$i] = $w[$i]->add($s1);
				++$i;
			}

			$a = $hash[0]->copy();
			$b = $hash[1]->copy();
			$c = $hash[2]->copy();
			$d = $hash[3]->copy();
			$e = $hash[4]->copy();
			$f = $hash[5]->copy();
			$g = $hash[6]->copy();
			$h = $hash[7]->copy();
			$i = 0;

			while ($i < 80) {
				$temp = array($a->bitwise_rightRotate(28), $a->bitwise_rightRotate(34), $a->bitwise_rightRotate(39));
				$s0 = $temp[0]->bitwise_xor($temp[1]);
				$s0 = $s0->bitwise_xor($temp[2]);
				$temp = array($a->bitwise_and($b), $a->bitwise_and($c), $b->bitwise_and($c));
				$maj = $temp[0]->bitwise_xor($temp[1]);
				$maj = $maj->bitwise_xor($temp[2]);
				$t2 = $s0->add($maj);
				$temp = array($e->bitwise_rightRotate(14), $e->bitwise_rightRotate(18), $e->bitwise_rightRotate(41));
				$s1 = $temp[0]->bitwise_xor($temp[1]);
				$s1 = $s1->bitwise_xor($temp[2]);
				$temp = array($e->bitwise_and($f), $g->bitwise_and($e->bitwise_not()));
				$ch = $temp[0]->bitwise_xor($temp[1]);
				$t1 = $h->add($s1);
				$t1 = $t1->add($ch);
				$t1 = $t1->add($k[$i]);
				$t1 = $t1->add($w[$i]);
				$h = $g->copy();
				$g = $f->copy();
				$f = $e->copy();
				$e = $d->add($t1);
				$d = $c->copy();
				$c = $b->copy();
				$b = $a->copy();
				$a = $t1->add($t2);
				++$i;
			}

			$hash = array($hash[0]->add($a), $hash[1]->add($b), $hash[2]->add($c), $hash[3]->add($d), $hash[4]->add($e), $hash[5]->add($f), $hash[6]->add($g), $hash[7]->add($h));
		}

		$temp = $hash[0]->toBytes() . $hash[1]->toBytes() . $hash[2]->toBytes() . $hash[3]->toBytes() . $hash[4]->toBytes() . $hash[5]->toBytes();

		if ($this->l != 48) {
			$temp .= $hash[6]->toBytes() . $hash[7]->toBytes();
		}

		return $temp;
	}

	public function _rightRotate($int, $amt)
	{
		$invamt = 32 - $amt;
		$mask = (1 << $invamt) - 1;
		return (($int << $invamt) & 4294967295) | (($int >> $amt) & $mask);
	}

	public function _rightShift($int, $amt)
	{
		$mask = (1 << (32 - $amt)) - 1;
		return ($int >> $amt) & $mask;
	}

	public function _not($int)
	{
		return ~$int & 4294967295;
	}

	public function _add()
	{
		static $mod;

		if (!isset($mod)) {
			$mod = pow(2, 32);
		}

		$result = 0;
		$arguments = func_get_args();

		foreach ($arguments as $argument) {
			$result += ($argument < 0 ? ($argument & 2147483647) + 2147483648 : $argument);
		}

		return fmod($result, $mod);
	}

	public function _string_shift(&$string, $index = 1)
	{
		$substr = substr($string, 0, $index);
		$string = substr($string, $index);
		return $substr;
	}
}

define('CRYPT_HASH_MODE_INTERNAL', 1);
define('CRYPT_HASH_MODE_MHASH', 2);
define('CRYPT_HASH_MODE_HASH', 3);

?>
