//
//  SearchView.swift
//  WhatFontis
//
//  Created by Addallah Hassan on 6/10/23.
//


import SwiftUI

struct SearchView: View {


	@State var service = SearchService()
	@EnvironmentObject var networkMonitor: NetworkMonitor//
	@State private var search = ""
	@State private var submitted = false
	@State private var errorMessage = ""
	@State private var scale = false
	@State var result: [resultOfSearch]?
	
	var body: some View {
		NavigationView {
			VStack{

				HStack{
					HStack(spacing: 3){
						Image(systemName: "magnifyingglass")
							.font(.callout)
							.foregroundColor(Color.primary)
						TextField("Search Fonts on the web", text: $search)
							.disabled(networkMonitor.isConnected ? false : true)
							.submitLabel(.search)
							.textInputAutocapitalization(.never)
							.disableAutocorrection(true)
							.onSubmit {
								if networkMonitor.isConnected {
									withAnimation {
										if search != "" {
											withAnimation {
												submitted = true
											}
										}
									}
									
									Task {
										do {
											result = try await service.searchFont(search: "\(search)")
										} catch myError.noInternet {
										 errorMessage = "Connection Appears to be Offline"
											return
										} catch myError.searchIsNill {
											errorMessage = "Search is Nill"
											return
										} catch myError.errorInEncoding {
											errorMessage = "Error in Encoding"
											return
										} catch myError.invalidURL {
											errorMessage = "invalid Url"
											return
										} catch myError.invalidResponse {
											errorMessage = "invalid Response"
											return
										} catch myError.errorInDecoding {
											errorMessage = "Error in Decoding"
											return
										}
									}
								}
							}
							.onChange(of: search) { _ in
								withAnimation{
									submitted = false
								}
							}
							
					}
					.padding(-2)
					

					
					if search != "" {
						Button {
							withAnimation {
								search = ""
								submitted = false
							}
						} label: {
							Image(systemName: "delete.backward.fill")
								.symbolRenderingMode(.hierarchical)
								.foregroundColor(.primary)
						}
					}
					
					if !networkMonitor.isConnected {
						Image(systemName: "wifi.slash")
							.font(.footnote)
							.foregroundColor(.pink)
							.symbolRenderingMode(.hierarchical)
							.scaleEffect(scale ? 1.7 : 1)
						
					}
				}
				.padding(8)
				.background(Color.primary.opacity(0.19))
				.cornerRadius(9)
				.padding(.horizontal,20)
				.onTapGesture {
					if !networkMonitor.isConnected {
						withAnimation(.spring()){
							scale = true
							DispatchQueue.main.asyncAfter(deadline: .now() + 0.2){
								withAnimation {
									scale = false
								}
							}
						}
					}
				}
				
				Spacer()
				
				
				if (!submitted && search == "") || (!submitted && search != "") || (submitted && search == ""){
					Text(errorMessage)
					Spacer()
					
				} else if networkMonitor.isConnected && submitted && search != "" {
					VStack{
						searchResults
					}
				}
			}
			.navigationTitle("Search")
			.environmentObject(networkMonitor)
		}
	}
	
	
	var searchResults: some View {
		ScrollView(showsIndicators: false){
			if result != nil {
				ForEach(result! ,id: \.self){ oneResult in
					Link(destination: URL(string: oneResult.url)!) {
						ZStack{
							AsyncImage(url: URL(string: oneResult.image )){ img in
								img
									.resizable()
									.scaledToFill()
									.cornerRadius(30)
									.padding()
								
							} placeholder: {
								LinearGradient(gradient: Gradient(colors: [.primary,.primary.opacity(0.7)]), startPoint: .bottom, endPoint: .top)
									.overlay{
										VStack(spacing: 10){
											ProgressView()
											Text("Searching")
										}
										.foregroundStyle(.secondary)
									}
									.transition(.opacity)
									.offset(y: -20)
								
							}
							.frame(width: UIScreen.main.bounds.width - 30 ,height: 180)
							
							
							VStack(alignment: .leading){
								Text(oneResult.title)
									.font(.title2)
									.foregroundColor(.secondary)
								
							}
							.padding()
							.frame(maxWidth: .infinity, maxHeight: 70,alignment: .leading)
							.background(.ultraThinMaterial)
							.frame(maxWidth: .infinity, maxHeight: .infinity, alignment: .bottom)
						}
						.mask{
							RoundedRectangle(cornerRadius: 20, style: .continuous)
						}
						.padding()
						.frame(height: 180)
					}
				}
			}
		}
	}
}


struct SearchView_Previews: PreviewProvider {
	static var previews: some View {
		SearchView()
			.environmentObject(NetworkMonitor())
	}
}
