/**
 * COMPLETE TRENDS TAB REPLACEMENT
 * 
 * INSTRUCTIONS:
 * 1. Open: capstone_frontend/src/pages/charity/Analytics.tsx
 * 2. Find: {/* Trends Tab *\/} (around line 1189)
 * 3. Replace everything from <TabsContent value="trends"...> to its closing </TabsContent>
 * 4. Paste this entire content
 */

{/* Trends Tab - Enhanced Trending & Activity Insights */}
<TabsContent value="trends" role="tabpanel" className="space-y-6">
  {/* Header Card with Key Metrics */}
  <Card className="bg-gradient-to-br from-background to-orange-500/5 border-orange-500/20">
    <CardContent className="pt-6">
      <div className="grid md:grid-cols-3 gap-4">
        <div className="p-4 rounded-xl bg-card/50 border shadow-sm">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-lg bg-orange-500/20">
              <TrendingUp className="h-5 w-5 text-orange-500" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Trending Period</p>
              <p className="text-lg font-bold">Last {trendingDays} Days</p>
            </div>
          </div>
        </div>
        <div className="p-4 rounded-xl bg-card/50 border shadow-sm">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-lg bg-green-500/20">
              <Activity className="h-5 w-5 text-green-500" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Active Campaigns</p>
              <p className="text-lg font-bold">{trending.length}</p>
            </div>
          </div>
        </div>
        <div className="p-4 rounded-xl bg-card/50 border shadow-sm">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-lg bg-blue-500/20">
              <BarChart3 className="h-5 w-5 text-blue-500" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Growth Types</p>
              <p className="text-lg font-bold">{growthByType.filter(g => g.growth_percentage > 0).length}</p>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>

  {/* Main Content: Trending Campaigns + Growth Metrics */}
  <div className="grid lg:grid-cols-2 gap-6">
    {/* Left: Trending Campaigns */}
    <Card className="hover:shadow-md transition-shadow duration-200 bg-gradient-to-br from-background to-orange-500/5">
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-xl font-bold flex items-center gap-2">
              <TrendingUp className="h-5 w-5 text-orange-500" />
              Top Trending Campaigns
            </h2>
            <p className="text-sm text-muted-foreground mt-1">Most active by donation activity</p>
          </div>
          <Select value={trendingDays.toString()} onValueChange={(v) => setTrendingDays(Number(v))}>
            <SelectTrigger className="w-28 h-9">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="7">7 days</SelectItem>
              <SelectItem value="30">30 days</SelectItem>
              <SelectItem value="90">90 days</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardHeader>
      <CardContent>
        {trending.length > 0 ? (
          <div className="space-y-3">
            {trending.slice(0, 5).map((campaign: any, index: number) => {
              const isTop = index === 0;
              
              return (
                <div 
                  key={campaign.id} 
                  className={`relative p-4 rounded-xl border transition-all duration-300 ${
                    isTop 
                      ? 'bg-gradient-to-r from-orange-500/10 to-amber-500/5 border-orange-500/30 shadow-md' 
                      : 'bg-card/50 hover:bg-accent/50 hover:shadow-sm hover:-translate-y-0.5'
                  }`}
                >
                  {isTop && (
                    <div className="absolute -top-2 -right-2">
                      <div className="bg-gradient-to-r from-orange-500 to-amber-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                        <TrendingUp className="h-3 w-3" />
                        <span>Hottest</span>
                      </div>
                    </div>
                  )}
                  
                  <div className="flex items-start justify-between gap-3">
                    <div className="flex items-start gap-3 flex-1">
                      <div className={`w-10 h-10 rounded-xl flex items-center justify-center text-xl ${
                        isTop ? 'bg-gradient-to-br from-orange-500 to-amber-600' : 'bg-primary/10'
                      }`}>
                        🔥
                      </div>
                      <div className="flex-1 min-w-0">
                        <h4 className="font-bold text-sm truncate mb-1">{campaign.title}</h4>
                        <div className="flex items-center gap-2 text-xs text-muted-foreground mb-2">
                          <span className="capitalize">{campaign.campaign_type?.replace('_', ' ')}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <div className="flex-1 h-2 bg-secondary rounded-full overflow-hidden">
                            <div
                              className="h-full bg-gradient-to-r from-orange-500 to-amber-500 transition-all duration-700"
                              style={{ width: `${Math.min(campaign.progress, 100)}%` }}
                            />
                          </div>
                          <span className="text-xs font-bold text-orange-500">{campaign.progress}%</span>
                        </div>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-xl font-extrabold text-green-500">
                        {campaign.donation_count}
                      </div>
                      <div className="text-[10px] text-muted-foreground">donations</div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <TrendingUp className="h-16 w-16 text-muted-foreground/30 mb-4" />
            <h3 className="text-lg font-semibold text-muted-foreground mb-2">No Trending Data</h3>
            <p className="text-sm text-muted-foreground">No campaigns with donation activity in this period</p>
          </div>
        )}
      </CardContent>
    </Card>

    {/* Right: Growth Rate by Campaign Type */}
    <Card className="hover:shadow-md transition-shadow duration-200 bg-gradient-to-br from-background to-blue-500/5">
      <CardHeader>
        <h2 className="text-xl font-bold flex items-center gap-2">
          <BarChart3 className="h-5 w-5 text-blue-500" />
          Growth Rate by Type
        </h2>
        <p className="text-sm text-muted-foreground mt-1">Month-over-month donation growth</p>
      </CardHeader>
      <CardContent>
        {growthByType.length > 0 ? (
          <>
            <div className="bg-card/50 rounded-lg p-4 border shadow-sm mb-4">
              <ResponsiveContainer width="100%" height={240}>
                <BarChart data={growthByType.slice(0, 6)} layout="horizontal">
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis type="number" tick={{ fontSize: 11 }} stroke="hsl(var(--muted-foreground))" />
                  <YAxis 
                    dataKey="label" 
                    type="category" 
                    width={110} 
                    tick={{ fontSize: 11 }} 
                    stroke="hsl(var(--muted-foreground))"
                  />
                  <Tooltip 
                    content={({ active, payload }) => {
                      if (active && payload && payload.length) {
                        const data = payload[0].payload;
                        return (
                          <div className="bg-popover border rounded-lg shadow-lg p-3">
                            <p className="font-semibold text-sm mb-1">{data.label}</p>
                            <p className="text-xs text-muted-foreground">
                              Growth: <span className={`font-bold ${data.growth_percentage > 0 ? 'text-green-500' : 'text-red-500'}`}>
                                {data.growth_percentage > 0 ? '+' : ''}{data.growth_percentage}%
                              </span>
                            </p>
                          </div>
                        );
                      }
                      return null;
                    }}
                  />
                  <Bar 
                    dataKey="growth_percentage" 
                    fill="#3B82F6" 
                    radius={[0, 6, 6, 0]}
                    name="Growth %"
                  >
                    {growthByType.slice(0, 6).map((entry: any, index: number) => (
                      <Cell 
                        key={`cell-${index}`} 
                        fill={entry.growth_percentage > 0 ? COLORS[index % COLORS.length] : '#94A3B8'}
                      />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div className="p-3 rounded-lg bg-green-500/10 border border-green-500/20">
                <p className="text-xs font-medium mb-1">Avg Growth</p>
                <p className="text-xl font-bold text-green-500">
                  +{(growthByType.filter((g: any) => g.growth_percentage > 0).reduce((sum: number, g: any) => sum + g.growth_percentage, 0) / (growthByType.filter((g: any) => g.growth_percentage > 0).length || 1)).toFixed(1)}%
                </p>
              </div>
              <div className="p-3 rounded-lg bg-blue-500/10 border border-blue-500/20">
                <p className="text-xs font-medium mb-1">Top Growth</p>
                <p className="text-sm font-bold text-blue-500 truncate">
                  {growthByType[0]?.label || 'N/A'}
                </p>
              </div>
            </div>
          </>
        ) : (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <BarChart3 className="h-16 w-16 text-muted-foreground/30 mb-4" />
            <p className="text-sm text-muted-foreground">No growth data available</p>
          </div>
        )}
      </CardContent>
    </Card>
  </div>

  {/* Most Improved Campaign Highlight */}
  {mostImproved && (
    <Card className="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 border-amber-500/30 shadow-md">
      <CardContent className="p-6">
        <div className="flex items-start gap-4">
          <div className="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
            <Award className="h-8 w-8" />
          </div>
          <div className="flex-1">
            <div className="flex items-center gap-2 mb-2">
              <h3 className="text-lg font-bold">🏆 Most Improved Campaign</h3>
              <span className="text-xs px-2 py-1 rounded-full bg-amber-500/20 text-amber-600 font-semibold">
                +{mostImproved.growth_percentage}% Growth
              </span>
            </div>
            <p className="text-2xl font-extrabold text-foreground mb-2">{mostImproved.title}</p>
            <p className="text-sm text-muted-foreground mb-3">
              <span className="font-semibold text-amber-600 capitalize">"{mostImproved.campaign_type?.replace('_', ' ')}"</span> campaign showed the highest month-over-month donation growth this month.
            </p>
            <div className="flex items-center gap-4 text-sm flex-wrap">
              <div className="flex items-center gap-2">
                <span className="text-muted-foreground">This month:</span>
                <span className="font-bold text-green-500">₱{mostImproved.current_month_total.toLocaleString()}</span>
              </div>
              <span className="text-muted-foreground">•</span>
              <div className="flex items-center gap-2">
                <span className="text-muted-foreground">Last month:</span>
                <span className="font-bold">₱{mostImproved.previous_month_total.toLocaleString()}</span>
              </div>
              <span className="text-muted-foreground">•</span>
              <div className="flex items-center gap-2">
                <span className="text-muted-foreground">Gain:</span>
                <span className="font-bold text-amber-600">₱{mostImproved.growth_amount.toLocaleString()}</span>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )}

  {/* Activity Timeline */}
  <Card className="hover:shadow-md transition-shadow duration-200">
    <CardHeader>
      <div className="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h2 className="text-xl font-bold flex items-center gap-2">
            <Calendar className="h-5 w-5 text-purple-500" />
            Campaign Activity Timeline
          </h2>
          <p className="text-sm text-muted-foreground mt-1">Daily activity trends over time</p>
        </div>
        <div className="flex items-center gap-2">
          <div className="flex bg-secondary rounded-lg p-1">
            <Button
              size="sm"
              variant={timelineView === 'campaigns' ? 'default' : 'ghost'}
              onClick={() => setTimelineView('campaigns')}
              className="h-8 text-xs"
            >
              Campaigns
            </Button>
            <Button
              size="sm"
              variant={timelineView === 'donations' ? 'default' : 'ghost'}
              onClick={() => setTimelineView('donations')}
              className="h-8 text-xs"
            >
              Donations
            </Button>
          </div>
          <Select value={timelineDays.toString()} onValueChange={(v) => setTimelineDays(Number(v))}>
            <SelectTrigger className="w-24 h-9">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="7">7 days</SelectItem>
              <SelectItem value="30">30 days</SelectItem>
              <SelectItem value="90">90 days</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      {activityTimeline.length > 0 ? (
        <>
          <div className="bg-card/50 rounded-lg p-4 border shadow-sm">
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={activityTimeline}>
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                <XAxis 
                  dataKey="date" 
                  tick={{ fontSize: 11 }} 
                  stroke="hsl(var(--muted-foreground))"
                  tickFormatter={(value) => {
                    const date = new Date(value);
                    return `${date.getMonth() + 1}/${date.getDate()}`;
                  }}
                />
                <YAxis tick={{ fontSize: 11 }} stroke="hsl(var(--muted-foreground))" />
                <Tooltip 
                  content={({ active, payload }) => {
                    if (active && payload && payload.length) {
                      const data = payload[0].payload;
                      return (
                        <div className="bg-popover border rounded-lg shadow-lg p-3">
                          <p className="font-semibold text-sm mb-2">{data.date}</p>
                          <p className="text-xs text-muted-foreground">
                            Campaigns: <span className="font-bold text-purple-500">{data.campaigns_created}</span>
                          </p>
                          <p className="text-xs text-muted-foreground">
                            Donations: <span className="font-bold text-green-500">{data.donations_received}</span>
                          </p>
                        </div>
                      );
                    }
                    return null;
                  }}
                />
                {timelineView === 'campaigns' ? (
                  <Line 
                    type="monotone" 
                    dataKey="campaigns_created" 
                    stroke="#8B5CF6" 
                    strokeWidth={3}
                    dot={{ fill: '#8B5CF6', r: 4 }}
                    activeDot={{ r: 6 }}
                    name="Campaigns Created"
                  />
                ) : (
                  <Line 
                    type="monotone" 
                    dataKey="donations_received" 
                    stroke="#10B981" 
                    strokeWidth={3}
                    dot={{ fill: '#10B981', r: 4 }}
                    activeDot={{ r: 6 }}
                    name="Donations"
                  />
                )}
                <Legend />
              </LineChart>
            </ResponsiveContainer>
          </div>

          <div className="grid md:grid-cols-3 gap-4 mt-4">
            <div className="p-4 rounded-lg bg-purple-500/10 border border-purple-500/20">
              <p className="text-xs font-medium mb-1">Total Campaigns</p>
              <p className="text-2xl font-bold text-purple-500">
                {activityTimeline.reduce((sum: number, d: any) => sum + (d.campaigns_created || 0), 0)}
              </p>
              <p className="text-xs text-muted-foreground">in last {timelineDays} days</p>
            </div>
            <div className="p-4 rounded-lg bg-green-500/10 border border-green-500/20">
              <p className="text-xs font-medium mb-1">Total Donations</p>
              <p className="text-2xl font-bold text-green-500">
                {activityTimeline.reduce((sum: number, d: any) => sum + (d.donations_received || 0), 0)}
              </p>
              <p className="text-xs text-muted-foreground">in last {timelineDays} days</p>
            </div>
            <div className="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20">
              <p className="text-xs font-medium mb-1">Total Amount</p>
              <p className="text-2xl font-bold text-amber-500">
                ₱{activityTimeline.reduce((sum: number, d: any) => sum + (d.donation_amount || 0), 0).toLocaleString()}
              </p>
              <p className="text-xs text-muted-foreground">in last {timelineDays} days</p>
            </div>
          </div>
        </>
      ) : (
        <div className="flex flex-col items-center justify-center py-12 text-center">
          <Calendar className="h-16 w-16 text-muted-foreground/30 mb-4" />
          <p className="text-sm text-muted-foreground">No activity data available</p>
        </div>
      )}
    </CardContent>
  </Card>

  {/* Summary Insight Card */}
  {(growthByType.length > 0 || trending.length > 0) && (
    <Card className="bg-gradient-to-r from-green-500/10 via-emerald-500/10 to-green-500/10 border-green-500/20">
      <CardContent className="p-6">
        <div className="flex items-start gap-3">
          <div className="p-2 rounded-lg bg-green-500/20">
            <TrendingUp className="h-5 w-5 text-green-500" />
          </div>
          <div className="flex-1">
            <p className="text-sm font-bold mb-1">Activity Summary</p>
            <p className="text-sm text-muted-foreground leading-relaxed">
              {growthByType.length > 0 && growthByType[0].growth_percentage > 0 ? (
                <>
                  <span className="font-semibold text-green-600">{growthByType[0].label}</span> campaigns are trending with a{' '}
                  <span className="font-bold text-foreground">+{growthByType[0].growth_percentage}%</span> rise in donations this month
                  {growthByType.length > 1 && growthByType[1].growth_percentage > 0 && (
                    <>, followed by <span className="font-semibold text-green-600">{growthByType[1].label}</span> campaigns (+{growthByType[1].growth_percentage}%)</>
                  )}. {trending.length > 0 && `${trending.length} campaigns are currently trending with active donation activity.`}
                </>
              ) : trending.length > 0 ? (
                <>{trending.length} campaigns are currently active with recent donation activity. Monitor growth trends to identify opportunities.</>
              ) : (
                <>Start creating campaigns and tracking donations to see trending insights and growth patterns!</>
              )}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  )}
</TabsContent>
