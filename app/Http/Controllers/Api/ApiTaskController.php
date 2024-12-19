<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;


class ApiTaskController extends Controller
{
    public function pdfReport()
    {
        $ongoingTasks = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'on_going')->where('user_id', auth()->user()->id)->get();
        $upcomingTasks = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'up_coming')->where('user_id', auth()->user()->id)->get();
        $doneTasks = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'done')->where('user_id', auth()->user()->id)->get();

        // Buat objek Dompdf
        $dompdf = new Dompdf();

        // Buat konten HTML untuk laporan
        $html = view('pdf.reportTask', compact('ongoingTasks', 'upcomingTasks', 'doneTasks'));

        // Muat konten HTML ke Dompdf
        $dompdf->loadHtml($html);

        // Atur ukuran dan orientasi halaman
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Output PDF ke browser atau simpan ke file
        $dompdf->stream('full_report.pdf');
    }

    public function addPriorityTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'reminder' => 'required|date_format:H:i',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'repeat' => 'required|in:Every Day,3 Days,7 Days',
            'to_do_list' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));

            $status = $this->determineStatus($start_date, $end_date);

            $data = Task::create([
                'user_id' => auth()->user()->id,
                'category_id' => '1',
                'title' => $request->title,
                'description' => $request->description,
                'reminder' => $request->reminder,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'repeat' => $request->repeat,
                'to_do_list' => json_encode($request->to_do_list),
                'to_do_list_status' => json_encode($request->to_do_list_status),
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Add Task Successfully!',
                'data' => $data,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Add Task Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function deletePriorityTask($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Pastikan pengguna memiliki hak akses untuk menghapus tugas
            if ($task->user_id !== auth()->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully!',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task!',
                'error' => $error->getMessage(),
            ], 500);
        }
    }


    private function determineStatus($start_date, $end_date)
    {
        $now = Carbon::now();
        $start_date = Carbon::createFromFormat('Y-m-d', $start_date);
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date);

        if ($now->lt($start_date)) {
            // Tanggal saat ini sebelum tanggal mulai
            return 'up_coming';
        } elseif ($now->between($start_date, $end_date)) {
            // Tanggal saat ini di antara tanggal mulai dan tanggal akhir
            return 'on_going';
        } else {
            // Tanggal saat ini setelah tanggal akhir
            return 'done';
        }
    }

    public function endPriorityTask($taskId)
    {
        try {
            Task::where('id', $taskId)->update([
                'status' => 'done',
            ]);

            $updatedTask = Task::find($taskId);

            return response()->json([
                'success' => true,
                'message' => 'End Task Successfully!',
                'data' => $updatedTask,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'End Task Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'to_do_list_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            Task::where('id', $taskId)->update([
                'to_do_list_status' => json_encode($request->to_do_list_status),
            ]);

            $updatedTask = Task::find($taskId);

            return response()->json([
                'success' => true,
                'message' => 'Updated Task Successfully!',
                'data' => $updatedTask,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Updated Task Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function updateDailyTaskStatusToFalse()
    {
        try {
            $task = Task::where('category_id', '2')->where('status', 'done')->update([
                'status' => 'on_going',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Updated Task Status Successfully!',
                'data' => $task,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Updated Task Status Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function updateDailyTaskStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            Task::where('id', $taskId)->update([
                'status' => $request->status,
            ]);

            $updatedTask = Task::find($taskId);

            return response()->json([
                'success' => true,
                'message' => 'Updated Task Status Successfully!',
                'data' => $updatedTask,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Updated Task Status Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function addDailyTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'reminder' => 'required|date_format:H:i',
            'repeat' => 'required|in:Every Day',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $data = Task::create([
                'user_id' => auth()->user()->id,
                'category_id' => '2',
                'title' => $request->title,
                'description' => $request->description,
                'reminder' => $request->reminder,
                'repeat' => $request->repeat,
                'status' => 'on_going',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Add Task Successfully!',
                'data' => $data,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Add Task Failed!',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function editPriorityTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'reminder' => 'required|date_format:H:i',
            // 'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'repeat' => 'required|in:Every Day,3 Days,7 Days',
            'to_do_list' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            // $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));

            Task::where('id', $request->id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'reminder' => $request->reminder,
                // 'start_date' => $start_date,
                'end_date' => $end_date,
                'repeat' => $request->repeat,
                'to_do_list' => json_encode($request->to_do_list),
                'to_do_list_status' => json_encode($request->to_do_list_status),
            ]);

            $updatedTask = Task::find($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Priority Task Updated Successfully',
                'data' => $updatedTask,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to Update Priority Task',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function editDailyTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'reminder' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            Task::where('id', $request->id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'reminder' => $request->reminder,
            ]);

            $updatedTask = Task::find($request->id);

            return response()->json([
                'success' => true,
                'message' => 'Daily Task Updated Successfully',
                'data' => $updatedTask,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to Update Daily Task',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function getAllPriorityTask()
    {
        $data = Task::orderBy('created_at')->where('category_id', '1')->whereIn('status', ['on_going', 'up_coming'])->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diload',
            'data' => $data
        ], 200);
    }

    public function getAllDailyTask()
    {
        $data = Task::orderBy('created_at')->where('category_id', '2')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diload',
            'data' => $data
        ], 200);
    }

    public function getPriorityTask()
    {
        $data = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'on_going')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diload',
            'data' => $data
        ], 200);
    }


    public function getUpComingPriorityTask()
    {
        $data = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'up_coming')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diload',
            'data' => $data
        ], 200);
    }

    public function getDonePriorityTask()
    {
        $data = Task::orderBy('created_at')->where('category_id', '1')->where('status', 'done')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diload',
            'data' => $data
        ], 200);
    }

    public function getPriorityTaskById($taskId)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('category_id', '1')
                ->where('user_id', auth()->user()->id)
                ->first();

            if ($task) {
                // tampilkan responsenya
                // menggunakan format json
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Data berhasil diload',
                        'data' => $task,
                    ],
                    200
                );
            }
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load task',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function getDailyTaskById($taskId)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('category_id', '2')
                ->where('user_id', auth()->user()->id)
                ->first();

            if ($task) {
                // tampilkan responsenya
                // menggunakan format json
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Data berhasil diload',
                        'data' => $task,
                    ],
                    200
                );
            }
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load task',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
