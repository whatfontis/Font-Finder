//
//  WhatFontisApp.swift
//  WhatFontis
//
//  Created by Addallah Hassan on 8/16/23.
//

import SwiftUI

@main
struct WhatFontisApp: App {
	@StateObject var networkMonitor = NetworkMonitor()
    var body: some Scene {
        WindowGroup {
            SearchView()
				.environmentObject(networkMonitor)
        }
    }
}
