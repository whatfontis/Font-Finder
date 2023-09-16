//
//  NetworkMonitor.swift
//  WhatFontis
//
//  Created by Addallah Hassan on 7/13/23.
//

import Foundation
import Network

//        MARK: - Just a Simple Class to check if the user is connected to the Internet.

@MainActor class NetworkMonitor: ObservableObject {
    private let networkMonitor = NWPathMonitor()
    private let workerQueue = DispatchQueue(label: "Monitor")
    var isConnected = false
    
    init() {
        networkMonitor.pathUpdateHandler = { path in
            self.isConnected = path.status == .satisfied
            Task {
                await MainActor.run {
                    self.objectWillChange.send()
                }
            }
        }
        networkMonitor.start(queue: workerQueue)
    }
}
