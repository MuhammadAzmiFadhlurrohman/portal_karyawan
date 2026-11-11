/*
 Navicat Premium Dump SQL

 Source Server         : Msn_Connection
 Source Server Type    : MySQL
 Source Server Version : 50742 (5.7.42-0ubuntu0.18.04.1)
 Source Host           : 103.161.207.5:3306
 Source Schema         : gomsn

 Target Server Type    : MySQL
 Target Server Version : 50742 (5.7.42-0ubuntu0.18.04.1)
 File Encoding         : 65001

 Date: 09/11/2026 10:56:08
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users_unified
-- ----------------------------
DROP TABLE IF EXISTS `users_unified`;
CREATE TABLE `users_unified`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nomor_telepon` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status` enum('Available','Working','Inactive') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Available',
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `role` enum('karyawan','atasan','admin','finance','CEO','NOC','HRD') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `divisi` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `no_rekening` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bank` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `atas_nama` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `kode_karyawan` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `auth_token` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  UNIQUE INDEX `nomor_telepon`(`nomor_telepon`) USING BTREE,
  UNIQUE INDEX `kode_karyawan`(`kode_karyawan`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 85 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_unified
-- ----------------------------
INSERT INTO `users_unified` VALUES (10, 'admin', NULL, 'Azmi Fadh', 'Available', NULL, '$2y$10$ulo5BznyV5Lzx9aWytXusOnK7.pujedQ8Ftfmc8dq1ovVrRgqZOMC', 'admin', 'Aplikasi', '', '', '', '', 'azmifariska04@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (29, 'Chriss', '087829724169', 'CHRISTIAN PERDI', 'Available', 'karyawan_29_1781452995.jpeg', '$2y$10$hfC63BLfm85Mg3Zgroewyeb.EJbH5Nl6fSjpt.t6NHx8Lqh.wJ3J.', 'karyawan', 'Teknik', '6768100374', 'BCA', 'Christian Perdy', 'KR20268', 'chrissferdy12@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (30, 'Ipin Aripin', '081234567890', 'Ipin Aripin', 'Available', NULL, '$2y$10$m.WeRH0yLl3JP/.CgLwiKOHeRyUKbeXEruIATeL3mW/UFSXIek2vO', 'atasan', 'Teknik', '', '', '', 'KR24521', 'ipinaripin219@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (31, 'harry estes', NULL, 'HARRY SETIONO', 'Available', 'karyawan_31_1768363791.jpg', '$2y$10$3fuefKm9O0banRyZTTLZ5eTXIFhDz5OViQfzlZ9qp5h27cPMUnNQ.', 'karyawan', 'NOC', '7772698120', 'BCA', 'HARRY SETIONO', 'KR31444', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (33, 'Dudi Durahman', '089655394872', 'DUDI DURAHMAN', 'Working', 'karyawan_33_1775634327.jpg', '$2y$10$K6gc6tojrusYJd9XbNQRiObJJok1kVg26SDIpGzzlRzgqJVL8Krnm', 'karyawan', 'Teknik', '4530205806', 'BCA', 'Dudi Durahman', 'KR38771', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (34, 'Padilah M Nur', '083190225326', 'M NUR PADILLAH', 'Working', 'karyawan_34_1769668582.jpg', '$2y$10$h1QD0Aaka.M.1FPwISDeuuos6sqoynQermGzXH2wSPinJc1Nbm9VO', 'karyawan', 'Teknik', '2781067166', 'BCA ', 'Lulu apriliani', 'KR43058', 'padilmuhammad53116@gamil.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (36, 'Amelia Agustina', NULL, 'Amelia Agustina', 'Available', NULL, '$2y$10$4tV7qzvObovBrbF2p8LZ9eq2aaJ0WGEws.svZB.PY5ntKih3LB8Tm', 'HRD', 'Finance', '', '', '', 'KR48567', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (37, 'rickysp', '082120808221', 'RICKY SAHARA PUTRA', 'Available', NULL, '$2y$10$3C0TzHKsJKDShNtjwZfx1.O4bmpRNHFET06LIXLtLr.iBF.3zTjRi', 'karyawan', 'NOC', '4380257144', 'BCA', 'ricky sahara putra', 'KR49730', 'rickyputraa03@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (39, 'Deni hamdani', NULL, 'DENI HAMDANI', 'Available', NULL, '$2y$10$r3jZaGOBLzKujeXlYGuUzeH.P2k97mBsJrlAKIELkGwXvp/r4kAWG', 'karyawan', 'Teknik', '4530254238', 'BCA', 'Deni hamdani', 'KR50880', 'denihamdeni1974@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (41, 'Depi Sopandi', NULL, 'DEPI SOPANDI', 'Available', NULL, '$2y$10$yn3kv5Ne6dJgku.B9F42OecgwHzGA/1FPcYjM1uDPrQx206FYGRlu', 'karyawan', 'Umum', '12345678', 'Bank IDN', 'Depi', 'KR51462', 'depi.sopandi22@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (42, 'Iyan Sofian', '081285347696', 'IYAN SOFIAN', 'Working', 'karyawan_42_1775634726.jpg', '$2y$10$8OEeEzqx8Eb8nqxtIA7LoeepDe1D0xcBn1KwsTfD0UdPB1Bg5izyu', 'karyawan', 'Teknik', '7772698049', 'BCA', 'Iyan Sofian', 'KR53995', 'jeunuchian@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (44, 'Bagus Joko', NULL, 'BAGUS JOKO PRIYONO', 'Available', NULL, '$2y$10$nYO10GUWKb1KIRCsITDwQOXp4QZBr5p0EO6EJQOCIhqazNNM5OwF6', 'atasan', 'NOC', '', '', '', 'KR59446', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (45, 'Dodi Sodikin', '089652270447', 'DODI SODIKIN', 'Working', 'karyawan_45_1767603094.jpg', '$2y$10$FLwPsWdEMxpqVAwRKBn6J.D1l84eWDxMLK509O51yMhYiNUW9Aasi', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'Dodi', 'KR62101', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (46, 'Finance', NULL, NULL, 'Available', NULL, '$2y$10$vPwnkrcP.4ibDjwkLvhWa.77ORkrn5PRuNpya8G7WcC6y9gCx6Yjm', 'finance', 'Finance', '', '', '', 'KR6465', 'karmelianditadharmawan@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (47, 'Encu samsudin', NULL, 'ENCU SAMSUDIN', 'Available', NULL, '$2y$10$sXkBQ.tC7FQcjRayUmqeU.7wVmj5b9hKpPrULPRnJ29MDFlHCigCq', 'karyawan', 'Teknik', '4531280452', 'BCA', 'Encu samsudin', 'KR68615', 'encu.samsudin.2525@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (48, 'Dika ibnu', '0859109830523', 'DIKA IBNU', 'Working', NULL, '$2y$10$IysdXlYPskmaW.yEQyBP5O2cKl.yQ.FVyZKhOYT90w7/Lapg/s0di', 'karyawan', 'Teknik', '4380432876', 'BCA', 'DIKA IBNU NAZRA YOGATAMA', 'KR68960', 'dikabmx5@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (49, 'Kelvin sultan', '0895606176870', 'KELVIN SULTAN', 'Available', 'karyawan_49_1768352465.jpg', '$2y$10$bJh52VC0DveQ2p4kQNxfvORLB3SjBy2F4SZt6p/uUm0Th4qMzWcsm', 'karyawan', 'NOC', '12345678', 'Bank IDN', 'Kevin', 'KR71580', 'kelvinsultan3@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (50, 'Ridwan Mustopa', NULL, 'RIDWAN MUSTOPA', 'Available', NULL, '$2y$10$HPzLLsVjzZiKXi5XYG5//ec0SPYNOFkj/Bt6Hqh0/4KEAy/.Dan0m', 'atasan', '..', '', '', '', 'KR72952', 'Ridwanmustopa76@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (51, 'mrafiramdhani', '082117138236', 'MUHAMAD RAFI RAMDANI', 'Available', NULL, '$2y$10$Bfsx6eePt6oXHvVatKLfjOUsw381ntMExRKkS1lEXlUfl3qR8ILCy', 'karyawan', 'NOC', '7751164401', 'BCA', 'MUHAMAD RAFI RAMDHANI', 'KR74528', 'rafiahmat272018@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (52, 'Levandri Ajmal', NULL, 'LEVANDRI AHMAD F AJMAL', 'Available', NULL, '$2y$10$KkYPDKvcPBIyEZkXWqWcUu/yTFf6G.l8LwIm1uhrqFXgOC3OBDDc6', 'atasan', 'NOC', '', '', '', 'KR7460', 'levandriajmal@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (55, 'Ryan Gondel', '083831228235', 'RYAN SEPTIADI', 'Working', 'karyawan_55_1776224631.jpg', '$2y$10$MiD.mgVdgZcTbE3ps0b9S.hsi9ROOQtaIbDTKoGrvsqqf2xqUUR1m', 'karyawan', 'Teknik', '8435056357', 'BCA', 'maria susanti', 'KR91030', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (56, 'Dede Rahman', '081320222935', 'DEDE RAHMAN', 'Working', 'karyawan_56_1775634510.jpg', '$2y$10$./csFGy.SXd5dznPYrHpeeFw3d2NbJ/0JXJeZXmkQNLY0pS1OG1/K', 'karyawan', 'Teknik', '00', '-', '-', 'KR92943', 'bettafisht21@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (57, 'Nunu Nugraha', NULL, 'NUNU NUGRAHA', 'Available', NULL, '$2y$10$r8dYI3tm1k9Pey3Gl2SPk.jrItgCmVppQ9zsANZl8ya4v5.CKBzsC', 'karyawan', 'Umum', '12345678', 'Bank IDN', 'Nunu', 'KR96674', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (58, 'Azmi Fadh', '081220562243', 'MUHAMMAD AZMI FADHLURROHMAN', 'Available', 'karyawan_58_1775633731.jpg', '$2y$10$J4x/OAJKYltAgSVJfpbzI.iSEp0.BOD929YJp/w5PYAX7dl6zTh3O', 'karyawan', 'Aplikasi', '1791226804', 'BNI', 'Muhammad Azmi Fadhlurrohman', 'KR92590', 'azmigudir@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (64, 'Sandy Wahyudi', '089665719400', 'SANDY WAHYUDY', 'Available', 'karyawan_64_1787579221.jpg', '$2y$10$jt0LadlDPjekIn.BB8Z.sOGNV1nHDMuyFeH7TxTXMD1jBujHZ7I0G', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'sandi', 'KR32144', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (65, 'Dandi Alrizki', '088971352212', 'DANDI ALRIZKI', 'Available', NULL, '$2y$10$64JF9mEHOKoUBVME3oHr3e0Xkmn1Wbg1GQPrnHDomdQdn6hKl/pJ2', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'Dandi', 'KR46054', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (66, 'Fendradika', '087735081997', 'FENDRADIKA', 'Available', NULL, '$2y$10$wR5fy/ep.3TzCg8voEXMcOUC1MQUobTIdkFs9pAdmACeLpROKuApi', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'Fendra', 'KR50881', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (67, 'Reza Apriant', '083155750975', 'REZA APRIANT', 'Available', 'karyawan_67_1767339932.jpg', '$2y$10$JUwxGrp0PeMa/ogDz1k40uurwx/XnCL0k5LM31SXdvPtzCsyZwJFm', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'Reza', 'KR55039', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (68, 'Abdul Ghani', '085624213955', 'ABDUL GHANI', 'Available', NULL, '$2y$10$lXl3KzTiC76jVN2Iqk42Ceyaat15Aweyps6eaFy2ZTIcHsASSTV7W', 'karyawan', 'Teknik', '12345678', 'Bank IDN', 'Abdul', 'KR90666', '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (69, 'NOC', '089696629955', 'NOC', 'Available', NULL, '$2y$10$0SQsIB3bGfpWfxMIGmAI0OKeUYjNTHTUIAHtkVHriCwM4teCvdY3.', 'NOC', 'NOC', NULL, NULL, NULL, NULL, 'noc@ptmsn.co.id', NULL, '2025-12-30 14:18:30');
INSERT INTO `users_unified` VALUES (70, 'Ida', '081234566788', 'Ida', 'Available', NULL, '$2y$10$Bp4IGyw3ETmsCowqr5/LJ.0wS14czPfqmv2uUaHYw/Gsc9ItkkiF6', 'atasan', 'Umum', NULL, NULL, NULL, 'KR64654', 'Ida@ptmsn.co.id', NULL, '2026-01-15 09:17:05');
INSERT INTO `users_unified` VALUES (71, 'Yudiana', NULL, 'YUDIANA ARIFIN', 'Available', NULL, '$2y$10$CuXGDn8c6tvsjw7uOtkJCOiDL48c92mW4o7XwK6REX4kKJvA39Vf2', 'CEO', 'Direktur', '', '', '', NULL, '-', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (72, 'amelia...', NULL, 'AMELIA AGUSTINA PUTRI', 'Available', NULL, '$2y$10$vs/Nh1Mi8b7AYDrZyAoYT.iil83WcN2uprsPk.43xd9JYTdOG2nJi', 'karyawan', 'Umum', '12345678', 'Bank IDN', 'Amelia', NULL, NULL, NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (73, 'Azmi', NULL, 'Muhammad Azmi', 'Available', NULL, '$2y$10$qSOFPUzkfM83QGzgIjLd2uG4BgsfL5CBiDytl.jjK.mp1D55tDV3i', 'atasan', 'Aplikasi', '', '', '', NULL, 'azmifariska04@gmail.com', NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (74, 'Iwan', NULL, 'Iwan', 'Available', NULL, '$2y$10$B5RD/MG51Y9.r2gkWgMg7e7NtAWCcY6/IVxUV1kcjQ1t6WXyzB3lC', 'karyawan', 'NOC', '00', 'Bank IDN', 'Iwan', 'KR31363', NULL, NULL, '2025-12-08 09:57:00');
INSERT INTO `users_unified` VALUES (79, 'adminnew', '081234567810', 'Admin Utama', 'Available', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-10 10:29:26');
INSERT INTO `users_unified` VALUES (80, 'WAWAN DARMAWAN', '085624951407', 'WAWAN DARMAWAN', 'Available', NULL, '$2y$10$ZWO9tfdaDL0cUisewS5SResvL7Iy97N3I.CpHXLS5a4RdjT2.klaK', 'karyawan', 'Teknik', NULL, NULL, NULL, 'KR8665', NULL, NULL, '2026-02-04 09:52:20');
INSERT INTO `users_unified` VALUES (81, 'Nur Rashif', '087834807491', 'MUHAMMAD HASYA NUR RASHIF', 'Available', 'karyawan_81_1775634919.jpg', '$2y$10$ztYIxWJPIfwc3v1AzqLAs.QGDUMhvdIdiOoEd6S0viH4kfSOH9wSe', 'karyawan', 'NOC', '00', 'bnk idn', 'rasyid', 'KR57502', 'muhammadrasyid764@gmail.com', NULL, '2026-03-26 13:31:38');
INSERT INTO `users_unified` VALUES (82, 'Wawan', '089650738427', 'Wawan Setiawan', 'Available', NULL, '$2y$10$HtKb5hW5SLbG4b/rVIqGoOfsANWWo/MWgwEtLom/N1KqXBQbAPWRe', 'karyawan', NULL, NULL, NULL, NULL, NULL, 'wawansetiawan201818@gmail.com', NULL, '2026-06-18 15:05:55');
INSERT INTO `users_unified` VALUES (83, 'Ohang', '082282526114', 'RAFI PUTRA RAMADHANI', 'Available', 'karyawan_83_1793837289.jpg', '$2y$10$jU56vxDtCSKnWWlDRdttqOE/L2fCxMAF89jo10JpHCjD91rS8.vkC', 'karyawan', 'Teknik', NULL, NULL, NULL, 'KR99092', 'rafi@ptmsn.co.id', NULL, '2026-10-22 11:00:03');
INSERT INTO `users_unified` VALUES (84, 'Budi', '085951332026', 'Budi Samsudin', 'Available', NULL, '$2y$10$DjQqlic17hJ5yqkmHIP5o.dUKoNhG8OF1yAtL9i2kF3fWiGgGF/Jq', 'karyawan', 'Teknik', NULL, NULL, NULL, 'KP092823', 'budisamsudin321@gmail.com', NULL, '2026-11-04 11:21:36');

SET FOREIGN_KEY_CHECKS = 1;
