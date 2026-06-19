-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Jun 19, 2026 at 05:36 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42088700_myfrienddb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cartID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `addedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cartID`, `userID`, `productID`, `quantity`, `addedAt`) VALUES
(1, 212, 10, 1, '2026-06-15 22:25:27'),
(17, 217, 26, 1, '2026-06-19 15:20:23'),
(18, 215, 28, 1, '2026-06-19 20:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaintID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `shopName` varchar(100) NOT NULL,
  `complaintReason` varchar(100) NOT NULL,
  `complaintMessage` text NOT NULL,
  `contactEmail` varchar(150) NOT NULL,
  `complaintStatus` varchar(30) NOT NULL DEFAULT 'Open',
  `createdAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaintID`, `userID`, `shopName`, `complaintReason`, `complaintMessage`, `contactEmail`, `complaintStatus`, `createdAt`) VALUES
(1, 213, 'naledi kitchen', 'Inappropriate Product', 'inapproriate product', 'tk@gmail.com', 'Investigating', '2026-06-17 16:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `resetID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `tokenHash` char(64) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `usedAt` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`resetID`, `userID`, `tokenHash`, `expiresAt`, `usedAt`, `createdAt`) VALUES
(1, 214, 'ab57b8b30b059d16d561168ad7000d3ff0ad3c238f9bc0d7d8ec447cc5c62b28', '2026-06-16 00:59:03', '2026-06-16 00:29:46', '2026-06-15 22:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `sellerID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `availableQuantity` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `sellerID`, `title`, `description`, `price`, `availableQuantity`, `image`, `shop_name`, `category`, `created_at`, `userID`) VALUES
(7, 211, 'Item 003', 'T-shirt', '300.00', 1, '003shirrt.jpeg', 'Vintage Apparel', 'Clothing', '2026-06-02 15:02:33', 0),
(9, 211, 'Radiohead Vintage T-Shirt', 'T-Shirt', '500.00', 1, '5edfe606945252e63f4230852e164007.jpg', 'Vintage Apparel', 'Clothing', '2026-06-02 15:09:07', 0),
(10, 211, 'Pink Floyd Vintage T-Shirt', 'T-Shirt', '550.00', 1, 'dark side of the moon.jpeg', 'Vintage Apparel', 'Clothing', '2026-06-02 15:11:11', 0),
(12, 209, 'GO-SLO\'S ', 'Chutney Flavored Maize Snack', '10.00', 1, '1781564325_go slo.png', 'SPAZABOY', 'Food', '2026-06-15 22:58:45', 0),
(13, 209, 'Nestle Bar One', '21g', '20.00', 1, '1781564675_bar one.png', 'SPAZABOY', 'Food', '2026-06-15 23:04:35', 0),
(14, 209, 'Yogueta Lollipop', '1 Yogueta Lollipop (All Flavours Available)', '2.50', 1, '1781564983_Yogueta.png', 'SPAZABOY', 'Food', '2026-06-15 23:09:43', 0),
(15, 212, 'Wors Roll', '1 Wors Roll with fried onion and a sauce of your choice (either mustard or tomato sauce)', '35.00', 1, '1781565927_wors roll.jpg', 'Star Naledi\'s Kitchen', 'Food', '2026-06-15 23:25:27', 0),
(16, 212, 'Delicious Pap and Wors Plate', 'Contains braai pap; boerewors; and chakalaka.', '80.00', 1, '1781566088_wors plate.jpg', 'Star Naledi\'s Kitchen', 'Food', '2026-06-15 23:28:08', 0),
(17, 212, 'Kota', 'Kota\r\n(DM for catalogue)', '30.00', 1, '1781566166_Kota-Popular-South-African-Food.jpg', 'Star Naledi\'s Kitchen', 'Food', '2026-06-15 23:29:26', 0),
(18, 3, 'u-Anyanisi', 'Onion Sack 1kg', '28.00', 1, '1781566741_onion sack.jpg', 'Jabulani Africa', 'Food', '2026-06-15 23:39:01', 0),
(21, 3, 'iZambane', 'Potato Sack 1kg', '29.00', 1, '1781567259_potato.png', 'Jabulani Africa', 'Food', '2026-06-15 23:47:39', 0),
(22, 3, 'uTamatisi', 'Bag of Tomatoes 1kg', '29.00', 1, '1781567660_bagged-tomatoes-2-786x524.jpg', 'Jabulani Africa', 'Food', '2026-06-15 23:54:20', 0),
(24, 213, 'Air Jordan 4 ', 'Air Jordan 4 (Real Makoya),\r\nSize 11, \r\nwith original box and tags. ', '3500.00', 1, '1781616164_jordan 4.jpg', 'DripForever', 'Clothing', '2026-06-16 13:22:44', 0),
(25, 213, 'Adidas Gucci NMD', 'Adidas Gucci NMD (Real Makoya),\r\nSize 8,\r\nwith box and tags.', '2000.00', 1, '1781616663_Adidas Gucci.jpg', 'DripForever', 'Clothing', '2026-06-16 13:31:03', 0),
(26, 213, 'Adidas Samba', 'Adidas Samba Black & White (Real Makoya), \r\nSize 7, \r\nwith box and tags.', '1500.00', 1, '1781616882_samba.jpg', 'DripForever', 'Clothing', '2026-06-16 13:34:42', 0),
(27, 214, 'iPhone 8 Plus', 'iPhone 8 Plus,\r\nUsed,\r\nBattery: 80%', '2500.00', 1, '1781617342_iPhone 8 Plus.png', 'Digital Pantsula', 'Electronics', '2026-06-16 13:42:22', 0),
(28, 214, 'iPhone 13', 'iPhone 13 White,\r\nUsed,\r\nBattery: 78%\r\n', '6200.00', 1, '1781617557_iPhone 13.jfif', 'Digital Pantsula', 'Electronics', '2026-06-16 13:45:57', 0),
(29, 215, 'HP laptop', 'Refurbished HP Laptop \r\n(DM for details)', '3450.00', 1, '1781618027_hp laptop.jpeg', 'neo4cheap', 'Electronics', '2026-06-16 13:53:47', 0);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transactionID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `amount` decimal(10,2) NOT NULL,
  `paymentStatus` varchar(50) DEFAULT 'Pending',
  `orderStatus` varchar(50) DEFAULT 'Pending Payment',
  `deliveryStatus` varchar(30) NOT NULL DEFAULT 'Pending',
  `transactionReference` varchar(80) DEFAULT NULL,
  `checkoutReference` varchar(80) DEFAULT NULL,
  `gatewayReference` varchar(100) DEFAULT NULL,
  `paidAt` datetime DEFAULT NULL,
  `transactionDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `buyerName` varchar(100) NOT NULL,
  `buyerPhone` varchar(20) NOT NULL,
  `buyerEmail` varchar(100) NOT NULL,
  `deliveryAddress` text NOT NULL,
  `paymentMethod` varchar(50) NOT NULL,
  `fulfilmentMethod` varchar(20) NOT NULL DEFAULT 'Delivery'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transactionID`, `buyerID`, `productID`, `quantity`, `amount`, `paymentStatus`, `orderStatus`, `deliveryStatus`, `transactionReference`, `checkoutReference`, `gatewayReference`, `paidAt`, `transactionDate`, `buyerName`, `buyerPhone`, `buyerEmail`, `deliveryAddress`, `paymentMethod`, `fulfilmentMethod`) VALUES
(21, 213, 29, 1, '3450.00', 'Paid', 'Completed', 'Delivered', 'MF-CART-20260617172843-3071-1', 'MF-CART-20260617172843-3071', NULL, '2026-06-19 12:52:12', '2026-06-17 21:28:43', 'tk', '0722928428', 'tk@gmail.com', '10 Viljoen St, 2 Die Aalwyne\r\nKrugersdorp North', 'Cash on Delivery', 'Delivery'),
(22, 213, 29, 1, '3450.00', 'Paid', 'Completed', 'Delivered', 'MF-CART-20260618170608-1472-1', 'MF-CART-20260618170608-1472', 'SANDBOX-RETURN-20260619155340', '2026-06-19 12:53:39', '2026-06-18 21:06:08', 'tk', '0722928428', 'tk@gmail.com', '10 Viljoen St, 2 Die Aalwyne\r\nKrugersdorp North', 'Online Payment', 'Delivery'),
(27, 215, 21, 1, '29.00', 'Paid', 'Completed', 'Delivered', 'MF-CART-20260619082121-9461-1', 'MF-CART-20260619082121-9461', 'SANDBOX-RETURN-20260619082201', '2026-06-19 05:22:01', '2026-06-19 12:21:22', 'Neo', '0722928428', 'neo22@gmail.com', '10 Viljoen St, 2 Die Aalwyne\r\nKrugersdorp North', 'Online Payment', 'Delivery'),
(28, 210, 29, 1, '3450.00', 'Paid', 'Completed', 'Delivered', 'MF-CART-20260619171045-2625-1', 'MF-CART-20260619171045-2625', 'SANDBOX-RETURN-20260619171115', '2026-06-19 14:11:16', '2026-06-19 21:10:46', 'bk', '0722928428', 'bk@gmail.com', '10 Viljoen St, 2 Die Aalwyne\r\nKrugersdorp North', 'Online Payment', 'Delivery');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shop_name` varchar(100) DEFAULT NULL,
  `sellerPhone` varchar(20) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `collectionAvailable` tinyint(1) NOT NULL DEFAULT 0,
  `collectionAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `name`, `email`, `password`, `role`, `created_at`, `shop_name`, `sellerPhone`, `province`, `city`, `area`, `collectionAvailable`, `collectionAddress`) VALUES
(1, 'John (admin)', 'myfriendjohn@gmail.com', '$2y$10$ansD2k3FuHsyAf80FD/0S.1akEtx6Z7TazZ1rC.udmVhfKET/QlT6', 'admin', '2026-05-19 17:27:41', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(3, 'Jabu', 'jabulanimadonsela@gmail.com', '$2y$10$wDfvfWEo4Pd6l7u9eeDKd.heNYWi/TzkZCg/TYIbNJffGFemtKN1.', 'seller', '2026-05-19 17:41:40', 'Jabulani Africa', '0675463241', 'KwaZulu-Natal', 'Durban', 'CBD', 1, '123 Main Road, Durban CBD'),
(209, 'Sipho', 'sipho47@gmail.com', '$2y$10$hOLi28jMXaafL7XKDpC3auw..82yfidmNNWe2czeipUbM0JyX8yOG', 'seller', '2026-05-20 13:53:19', 'SPAZABOY', '0624489761', 'Gauteng', 'Johannesburg', 'CBD', 1, '32 Market St, Braamfontein'),
(210, 'bk', 'bk@gmail.com', '$2y$10$x3YbxfBI56dE6Qxs88ssMuXx6SL3f6PExhGk9.irIsKStsdWIO3mm', 'seller', '2026-05-26 10:12:23', 'BoksBurger', '0868655432', 'Gauteng', 'Johannesburg', 'CBD', 1, '22 Love St, Braamfontein'),
(211, 'Bokang Kelebonye', 'bk2@gmail.com', '$2y$10$yzWr.cZiAfRFT9bd9wx6ieFhm8tppBhM5rl1IEzECXfLEm7s6mVx2', 'seller', '2026-06-02 13:16:24', 'Vintage Apparel', '0876543210', 'Gauteng', 'Johannesburg', 'Braamfontein', 1, '10 Begin St, Braamfontein'),
(212, 'Naledi Mokel', 'naledim@gmail.com', '$2y$10$jEtDat5G5MCqsqCL.OfnE.ZTZbHk4gceQLc4uylJK9YCn5e/tqaAm', 'seller', '2026-06-02 13:16:47', 'Star Naledi\'s Kitchen', '0755988934', 'Gauteng', 'Johannesburg', 'Midrand', 1, '14 Alsation Rd, Glen Austin'),
(213, 'tk', 'tk@gmail.com', '$2y$10$BQuMl/k7vHxFf7g/zCqUCeHTNuXBhDg5/6EOpyB6vrbi7eHdK0U9G', 'seller', '2026-06-15 12:56:02', 'DripForever', '0876334561', 'Gauteng', 'Johannesburg', 'Braamfontein', 1, '337 MTN St, Braamfontein'),
(214, 'Bokang', 'bokang@gmail.com', '$2y$10$F7MoNEt6qouCfXDmddt/cOlPO09S9XGd2H..xvyz0yRrRApnrFjD.', 'seller', '2026-06-15 22:28:02', 'Digital Pantsula', '0733837537', 'North West', 'Rustenburg', 'Magaliesburg', 0, NULL),
(215, 'Neo', 'neo22@gmail.com', '$2y$10$vEecZoVCxlg.erolbWYwCu0fYp6EZnqro6luWjsZdTIcPPXTfKVem', 'seller', '2026-06-16 13:48:40', 'neo4cheap', '0662296548', 'Western Cape', 'Cape Town', 'CBD', 1, '20 Viljoen St, Cape Town'),
(216, 'simphiwe', 'simp@gmail.com', '$2y$10$f8E1Hoc950IOf0VCurn4G.1Imaf2regvhjEzx./UJlYPnUW2MA7/6', 'buyer', '2026-06-16 14:26:46', NULL, NULL, NULL, NULL, NULL, 0, NULL),
(217, 'Oratile', 'oratile@gmail.com', '$2y$10$k5C4f9cmzPGJBOnXfeDeve8zSVDm89f1d8ylwa.djFACV.xeWda8q', 'buyer', '2026-06-19 15:13:30', NULL, NULL, NULL, NULL, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cartID`),
  ADD UNIQUE KEY `uq_cart_user_product` (`userID`,`productID`),
  ADD KEY `fk_cart_product` (`productID`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaintID`),
  ADD KEY `fk_complaint_user` (`userID`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`resetID`),
  ADD UNIQUE KEY `tokenHash` (`tokenHash`),
  ADD KEY `idx_password_reset_user` (`userID`),
  ADD KEY `idx_password_reset_expiry` (`expiresAt`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `sellerID` (`sellerID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transactionID`),
  ADD UNIQUE KEY `uq_transaction_reference` (`transactionReference`),
  ADD KEY `buyerID` (`buyerID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaintID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `resetID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`buyerID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

