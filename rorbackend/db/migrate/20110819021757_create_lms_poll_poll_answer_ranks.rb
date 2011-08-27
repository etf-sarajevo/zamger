class CreateLmsPollPollAnswerRanks < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_answer_ranks do |t|
      t.integer :poll_result_id
      t.integer :poll_question_id
      t.integer :poll_question_choice_id

      # t.timestamps
    end
  end
end
