/* CUSTOM VARS START */
/* REAL_TABLE_NAME: `wp_zbs_quotes`; */
/* PRE_TABLE_NAME: `1668940036_wp_zbs_quotes`; */
/* CUSTOM VARS END */

CREATE TABLE IF NOT EXISTS `1668940036_wp_zbs_quotes` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `zbs_site` int(11) DEFAULT NULL, `zbs_team` int(11) DEFAULT NULL, `zbs_owner` int(11) NOT NULL, `zbsq_id_override` varchar(128) DEFAULT NULL, `zbsq_title` varchar(255) DEFAULT NULL, `zbsq_currency` varchar(4) NOT NULL DEFAULT '-1', `zbsq_value` decimal(18,2) DEFAULT '0.00', `zbsq_date` int(14) NOT NULL, `zbsq_template` varchar(200) DEFAULT NULL, `zbsq_content` longtext, `zbsq_notes` longtext, `zbsq_hash` varchar(64) DEFAULT NULL, `zbsq_send_attachments` tinyint(1) NOT NULL DEFAULT '-1', `zbsq_lastviewed` int(14) DEFAULT '-1', `zbsq_viewed_count` int(10) DEFAULT '0', `zbsq_accepted` int(14) DEFAULT '-1', `zbsq_acceptedsigned` varchar(200) DEFAULT NULL, `zbsq_acceptedip` varchar(64) DEFAULT NULL, `zbsq_created` int(14) NOT NULL, `zbsq_lastupdated` int(14) NOT NULL, PRIMARY KEY (`ID`), KEY `title` (`zbsq_title`), KEY `dateint` (`zbsq_date`), KEY `hash` (`zbsq_hash`), KEY `created` (`zbsq_created`), KEY `accepted` (`zbsq_accepted`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
INSERT INTO `1668940036_wp_zbs_quotes` (`ID`, `zbs_site`, `zbs_team`, `zbs_owner`, `zbsq_id_override`, `zbsq_title`, `zbsq_currency`, `zbsq_value`, `zbsq_date`, `zbsq_template`, `zbsq_content`, `zbsq_notes`, `zbsq_hash`, `zbsq_send_attachments`, `zbsq_lastviewed`, `zbsq_viewed_count`, `zbsq_accepted`, `zbsq_acceptedsigned`, `zbsq_acceptedip`, `zbsq_created`, `zbsq_lastupdated`) VALUES (1,1,1,5,'','New Quote','',500,1668124800,1,'<h2>Summary</h2>\r\n<p>A paragraph summary, such as: Works to website at clientsdomain.com, including a re-design with three client-feedback stages, and translation into French.</p>\r\n<h2>Strategic Goals</h2>\r\n<p>’s experience is your secret advantage. We’re super aware of what’s necessary to present a strong online presence in 2017. We’ve been doing this for more than a decade, and the number one reason our clients come back time and time again is that we know that your strategic goals are our strategic goals.</p>\r\n<p>At John Smith, you’ve told us that you need to refine your online experience, and to improve engagement. We will deliver on these goals, and have applied our years of practical experience, and broken these down into deliverables, each marking a stage of completion where you can feedback. We’ve also analysed your requests and come up with some concrete markers which we can use to ensure we over deliver!:</p>\r\n<ul>\r\n<li>Refine online experience @ clientsdomain.com\r\n<ul>\r\n<li>Marker: User Experience feedback score before and after work, with a 30% improvement overall</li>\r\n</ul>\r\n</li>\r\n<li>Improve engagement.\r\n<ul>\r\n<li>Marker: Bail-rate reduced by 50%</li>\r\n</ul>\r\n</li>\r\n</ul>\r\n<h2>Deliverables</h2>\r\n<p>A strong online presence is fundamental to John Smith’s business, so we’re suggesting this work take place over three stages, with a trial run, or soft-launch, to protect your brand.</p>\r\n<ol>\r\n<li>First-fix of new design delivered to you and your team for feedback (2 week turnaround)</li>\r\n<li>Feedback acted-upon, design re-presented to larger stakeholder group (2 week turnaround)</li>\r\n<li>Soft-launch to 25% of traffic to your main website (2 week turnaround)</li>\r\n<li>Final handover to your in-house developer</li>\r\n</ol>\r\n<h2>Quotation</h2>\r\n<table>\r\n\r\n<tr>\r\n<td>Item</td>\r\n<td>Cost</td>\r\n</tr>\r\n<tr>\r\n<td>Item 1</td>\r\n<td>$0.00</td>\r\n</tr>\r\n<tr>\r\n<td>Item 2</td>\r\n<td>$0.00</td>\r\n</tr>\r\n<tr>\r\n<td>New Quote Total</td>\r\n<td>$500.00</td>\r\n</tr>\r\n\r\n</table>\r\n<p>(To be paid as follows…)</p>\r\n<ul>\r\n<li>Deposit: 40%</li>\r\n<li>Stage 1 payment (on delivery of deliverable 1): 10%</li>\r\n<li>Stage 2 payment (on delivery of deliverable 2): 20%</li>\r\n<li>Final payment (balance, on completion of deliverables): 30%</li>\r\n</ul>\r\n<h2>Getting Started</h2>\r\n<p>This quotation, dated 11/11/2022, is available for immediate commencement. When you are ready to proceed, please do accept below by entering your email address and hitting accept.</p>\r\n<p>If you would like an amendment, or to discuss details, please do let us know via email (your@email.com), or phone (01234 567 891).</p>\r\n<p>Once accepted, will schedule a commencement meeting with John Smith, check over the initial details, and then proceed with the works as stated above.</p>\r\n<h2>Terms and Conditions</h2>\r\n<p><strong>NOTE:</strong> We recommend that you write these yourself, as these are your legal protection in any dispute with your client. It is worth firming up dates, and client components of your “deliverables” here – e.g. if they need to send you a logo before you can finish stage 1, write a clause which says “we need a high-res logo by date X, otherwise the stage will be classed as complete”.</p>','','sUzQjGXpC4b4IOfYcmK',-1,0,0,0,'','',1668187212,1668187212);
