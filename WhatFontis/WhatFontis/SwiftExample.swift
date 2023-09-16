//
//  SwiftExample.swift
//
//  Created by Addallah Hassan on 8/16/23.
//

import Foundation
import SwiftUI


//        MARK: - A Model of the JSON file that we're going to send.
struct SearchedFonts: Codable {
	let FONT: ReturnedFont
}

struct ReturnedFont: Codable{
	let API_KEY: String
	let INFO: Info
}

struct Info: Codable {
	let TITLE: String
	let letters: [Letter]
}

struct Letter: Codable {
	let letter: String
	let url: String
}

//        MARK: - A Model of the JSON file that we're going to get Bakc.
struct resultOfSearch: Codable ,Hashable {
	let title: String
	let url: String
	let image: String
}


class SearchService: NetworkMonitor {
	
	func searchFont(search: String) async throws -> [resultOfSearch] {
		
		guard !NetworkMonitor().isConnected else { throw myError.noInternet }
		
		guard search != "" else { throw myError.searchIsNill }
		
		//		GO TO WHATFONTIS WEBSITE > CREATE AN ACCOUNT > SCROLL TO BOTTON > CLICK THE API AND YOU SHOULF FIND YOUR API KEY THERE.
		//		https://whatfontis.com/api
		
#error("Add your API Key")
		let font = SearchedFonts(FONT: ReturnedFont(API_KEY: "--ENTER YOUR API KEY HERE--", INFO: Info(TITLE: "\(search)", letters: [
			Letter(letter: "F", url: "https://www.whatfontis.com/api/img/F.png"),
			Letter(letter: "o", url: "https://www.whatfontis.com/api/img/o.png"),
			Letter(letter: "n", url: "https://www.whatfontis.com/api/img/n.png"),
			Letter(letter: "t", url: "https://www.whatfontis.com/api/img/t.png")
			
		])))
		
		var endpoint: String
		var encodedData: Data = Data()
		let encoder = JSONEncoder()
		let decoder = JSONDecoder()
		var result: [resultOfSearch] = []
		
		do {
			encoder.outputFormatting = .withoutEscapingSlashes
			encodedData = try encoder.encode(font)
			
		} catch {
			print("error while ENCODING data: \(error.localizedDescription)")
			throw myError.errorInEncoding
		}
		
		
		//        MARK: - Find a way to host the file before trying to
		let file = String(data: encodedData, encoding: .utf8)
		
		
		do {
		//        MARK: - file must be someWhere in the web !!
			
#error("Add the url to the JSON file")
			endpoint = "https://www.whatfontis.com/api/?file=\("file url")&limit=10"
			//				endpoint = "https://www.whatfontis.com/api/?file=\(a link to the file)&limit=10"
			
//			For testing purposes you can use service like JSONkeeper or nPoint ˆˆˆˆˆˆˆˆˆˆˆˆˆˆˆˆˆˆ
			
			guard let url = URL(string: endpoint) else {
				print("invalidURL")
				throw myError.invalidURL
			}
			
			let (data, response) = try await URLSession.shared.data(from: url)
			
			guard let response = response as? HTTPURLResponse, response.statusCode == 200 else {
				print("response Error !")
				throw myError.invalidResponse
			}
			
			result = try decoder.decode([resultOfSearch].self, from: data)
			print(result)
			
		} catch {
			print("error while DECODING data: \(error.localizedDescription)")
			throw myError.errorInDecoding
		}
		
		
		return result
	}
}




enum myError: Error {
	case noInternet
	case invalidURL
	case invalidResponse
	case genericError
	case errorInEncoding
	case errorInDecoding
	case searchIsNill
}

