@extends('layouts.app')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container-fluid">
        <div class="card">
            <div class="card-header row">
                <div class="col">
                    <h4>Activity</h4>
                </div>
                <div class="col text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCompetitionModal">Add
                        New</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Venue</th>
                                <th>Target Audience</th>
                                <th>Activity Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $activityTypes = [
                                    \App\Enums\ActivityType::Audition => 'Audition',
                                    \App\Enums\ActivityType::Tryout => 'Tryout',
                                    \App\Enums\ActivityType::Practice => 'Practice',
                                    \App\Enums\ActivityType::Competition => 'Competition',
                                ];
                            @endphp
                            @foreach ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->title }}</td>
                                    <td>{{ $activityTypes[$activity->type] ?? 'Unknown Type' }}</td>
                                    <td>{{ $activity->venue }}</td>
                                    @if ($activity->target_player == 0)
                                        <td>All Students</td>
                                        @else
                                        <td>Official Players</td>
                                    @endif
                                    <td>
                                        {{ \Carbon\Carbon::parse($activity->start_date)->format('F d, Y h:i A') }} -
                                        {{ \Carbon\Carbon::parse($activity->end_date)->format('F d, Y h:i A') }}
                                    </td>
                                    <td>
                                        @if ($activity->status == 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif ($activity->status == 0)
                                            <span class="badge text-black" style="background: yellow">Pending</span>
                                        @elseif ($activity->status == 2)
                                            <span class="badge bg-danger">Declined</span>
                                        @endif
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#editCompetitionModal"
                                            onclick="loadActivityData({{ $activity->id }})">Edit</button>
                                        <button type="button" class="btn btn-info viewBtn" data-bs-toggle="modal"
                                            data-bs-target="#viewActivityModal" data-id="{{ $activity->id }}">
                                            View
                                        </button>
                                        <button type="button" class="btn btn-danger">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="newCompetitionModal" tabindex="-1" aria-labelledby="newCompetitionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCompetitionModalLabel">Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form inside modal -->
                    <form action="{{ route('activity.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <!-- Title -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" placeholder="Title" name="title" required>
                            </div>
                            <!-- Target Players -->
                            <div class="col-md-6">
                                <label for="target_players" class="form-label">Target players</label>
                                <select class="form-select" name="target_player" required>
                                    <option value="" disabled selected>Select target</option>
                                    <option value="0">All Student</option>
                                    <option value="1">Official Player</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" name="content" placeholder="Content" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="form-group col">
                                <label for="activity_type" class="form-label">Activity Type</label>
                                <select class="form-select" name="type" required>
                                    @if (auth()->user()->hasRole('adviser'))
                                        <option value="0">Audition</option>
                                        <option value="2">Practice</option>
                                    @endif
                                    @if (auth()->user()->hasRole('coach'))
                                        <option value="1">Tryout</option>
                                        <option value="2">Practice</option>
                                    @endif
                                    @if (auth()->user()->hasRole(['admin_sport', 'admin_org']))
                                        <option value="3" selected>Competition</option>
                                    @endif
                                </select>
                            </div>
                            @coach
                                <div class="form-group col">
                                    <label for="organization">Sport</label>
                                    <input type="text" value="{{ $user->sport->name }}" class="form-control"
                                        placeholder="Organization" readonly>
                                </div>
                            @endcoach
                            @adviser
                                <div class="form-group col">
                                    <label for="organization">Organization</label>
                                    <input type="text" value="{{ $user->organization->name }}" class="form-control"
                                        placeholder="Organization" readonly>
                                </div>
                            @endadviser
                        </div>
                        <div class="row mb-3 mt-3">
                            <!-- Activity Start Date -->
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Activity Start Date</label>
                                <input type="datetime-local" class="form-control" name="start_date" required>
                            </div>

                            <!-- Activity End Date -->
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Activity End Date</label>
                                <input type="datetime-local" class="form-control" name="end_date" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- Venue -->
                            <div class="col-md-12">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" placeholder="Venue" name="venue" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <!-- Address -->
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" placeholder="Address" required>
                        </div>

                        <div class="mb-3">
                            <!-- Attachment -->
                            <label for="attachment" class="form-label">Attachment (Image)</label>
                            <input class="form-control" type="file" name="attachment[]" accept="image/*" multiple>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Modal --}}
    <div class="modal fade" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewActivityModalLabel">View Activity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Read-only Form -->
                    <div class="row mb-3">
                        <!-- Title -->
                        <div class="col-md-6">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="view_title" value="" readonly>
                        </div>
                        <!-- Target Players -->
                        <div class="col-md-6">
                            <label for="target_players" class="form-label">Target players</label>
                            <input type="text" class="form-control"
                                id="view_target_players" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" rows="3" readonly id="view_content"></textarea>
                    </div>

                    <div class="row">
                        <div class="form-group col">
                            <label for="activity_type" class="form-label">Activity Type</label>
                            <input type="text" class="form-control" value="" id="view_type"
                                readonly>
                        </div>
                        @if (auth()->user()->hasRole('coach'))
                            <div class="form-group col">
                                <label for="organization">Sport</label>
                                <input type="text" value="{{ auth()->user()->sport->name }}" id="view_sport_id" class="form-control" readonly>
                            </div>
                        @elseif (auth()->user()->hasRole('adviser'))
                            <div class="form-group col">
                                <label for="organization">Organization</label>
                                <input type="text" value="" id="view_organization_id" class="form-control"
                                    readonly>
                            </div>
                        @endif
                    </div>

                    <div class="row mb-3 mt-3">
                        <!-- Activity Start Date -->
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Activity Start Date</label>
                            <input type="text" class="form-control"
                                value="" id="view_start_date" readonly>
                        </div>

                        <!-- Activity End Date -->
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Activity End Date</label>
                            <input type="text" class="form-control"
                                value="" id="view_end_date" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Venue -->
                        <div class="col-md-12">
                            <label for="venue" class="form-label">Venue</label>
                            <input type="text" class="form-control" value="" id="view_venue" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <!-- Address -->
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" value="" id="view_address" readonly>
                    </div>

                    {{-- <div class="mb-3">
                        <!-- Attachment -->
                        <label for="attachment" class="form-label">Attachment (Images)</label>
                        <div class="row">
                            @if ($activity->attachments)
                                @foreach ($activity->attachments as $attachment)
                                    <div class="col-md-3">
                                        <img src="{{ asset('storage/' . $attachment) }}" class="img-fluid img-thumbnail"
                                            alt="Attachment Image">
                                    </div>
                                @endforeach
                            @else
                                <p>No attachments available.</p>
                            @endif
                        </div>
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <!-- Close Button -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- Back Button -->
                    {{-- <a href="{{ route('activity.index') }}" class="btn btn-primary">Back</a> --}}
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Modal -->
    <div class="modal fade" id="editCompetitionModal" tabindex="-1" aria-labelledby="editCompetitionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompetitionModalLabel">Edit Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form inside modal -->
                    <form action="" id="editActivityForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <!-- Title -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" placeholder="Title" id="title"
                                    name="title" required>
                            </div>
                            <!-- Target Players -->
                            <div class="col-md-6">
                                <label for="target_players" class="form-label">Target players</label>
                                <select class="form-select" id="target_players" name="target_player" required>
                                    <option value="" disabled selected>Select target</option>
                                    <option value="0">All Student</option>
                                    <option value="1">Official Player</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" placeholder="Content" rows="3" required></textarea>
                        </div>

                        <div class="">
                            <label for="activity_type" class="form-label">Activity Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="3" selected>Competition</option>
                            </select>
                        </div>
                        <div class="row mb-3 mt-3">
                            <!-- Activity Start Date -->
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Activity Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                                    required>
                            </div>

                            <!-- Activity End Date -->
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Activity End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <!-- Venue -->
                            <div class="col-md-12">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" id="venue" placeholder="Venue"
                                    name="venue" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <!-- Address -->
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                placeholder="Address" required>
                        </div>

                        <div class="mb-3">
                            <!-- Attachment -->
                            <label for="attachment" class="form-label">Attachment (Image)</label>
                            <input class="form-control" type="file" id="attachment" name="attachment[]"
                                accept="image/*" multiple>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $('#datatable').DataTable();

        function loadActivityData(activityId) {
            // Call the show route using AJAX
            $.ajax({
                url: '/activity/' + activityId, // Assuming your route is like /activity/{id}
                method: 'GET',
                success: function(data) {
                    // Populate the modal fields with data
                    $('#editActivityForm #title').val(data.title);
                    $('#editActivityForm #target_players').val(data.target_player);
                    $('#editActivityForm #content').val(data.content);
                    $('#editActivityForm #type').val(data.type);
                    $('#editActivityForm #start_date').val(data.start_date);
                    $('#editActivityForm #end_date').val(data.end_date);
                    $('#editActivityForm #venue').val(data.venue);
                    $('#editActivityForm #address').val(data.address);

                    // Set the form action dynamically for updating the activity
                    $('#editActivityForm').attr('action', '/activity/' + activityId);
                },
                error: function(xhr) {
                    console.error('Error fetching activity data', xhr);
                    alert('Failed to load activity data.');
                }
            });
        }

        $(() => {
            $('.viewBtn').click(function () {
                fetch('/activity/' + $(this).data('id'))
                .then(response => response.json())
                .then(activity => {
                    $('#view_title').val(activity.title)
                    $('#view_target_players').val(activity.target_player)
                    $('#view_content').val(activity.content)
                    $('#view_type').val(activity.type)
                    $('#view_start_date').val(activity.start_date)
                    $('#view_end_date').val(activity.end_date)
                    $('#view_venue').val(activity.venue)
                    $('#view_address').val(activity.address)
                })
            })
        })
    </script>
@endpush
